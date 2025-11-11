<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ProjectController - Mengelola CRUD untuk Project
 * 
 * Controller ini menghandle semua operasi CRUD untuk Project:
 * - Menampilkan daftar project (index)
 * - Membuat project baru (create & store)
 * - Menampilkan detail project (show)
 * - Mengedit project (edit & update)  
 * - Menghapus project (destroy)
 */
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * Fungsi ini menampilkan daftar semua project yang ada dengan relasi:
     * - User yang membuat project (creator)
     * - Jumlah anggota tim di project
     * - Status deadline (overdue, due soon, safe)
     */
    public function index()
    {
        // Mengambil semua project dengan relasi untuk optimasi query (Eager Loading)
        $projects = Project::with([
            'creator:id,full_name,email', // Relasi ke User yang membuat project
            'members:id,project_id,user_id,role', // Relasi ke anggota project
            'members.user:id,full_name' // Relasi ke data user dari anggota
        ])
        ->withCount('members') // Menghitung jumlah anggota
        ->orderBy('created_at', 'desc') // Urutkan berdasarkan yang terbaru
        ->get()
        ->map(function ($project) {
            // Menghitung status deadline untuk setiap project
            $deadline = Carbon::parse($project->deadline);
            $now = Carbon::now();
            
            if ($deadline->isPast()) {
                $project->deadline_status = 'overdue';
                $project->deadline_color = 'text-red-600';
            } elseif ($deadline->diffInDays($now) <= 7) {
                $project->deadline_status = 'due_soon';
                $project->deadline_color = 'text-yellow-600';
            } else {
                $project->deadline_status = 'safe';
                $project->deadline_color = 'text-green-600';
            }
            
            return $project;
        });
        
        //  dd($projects);
        return view('projects.index', compact('projects'));
    }













    /**
     * Show the form for creating a new resource.
     * 
     * Menampilkan form untuk membuat project baru.
     * Form ini akan mengirim data ke method store() saat di-submit.
     */
    public function create()
    {
        // Mengambil daftar users untuk dropdown anggota tim (opsional)
        $users = User::select('id', 'full_name', 'email')
            ->where('id', '!=', Auth::id()) // Exclude user yang sedang login
            ->orderBy('full_name')
            ->get();

        return view('projects.create', compact('users'));
    }











    /**
     * Store a newly created resource in storage.
     * 
     * Menyimpan project baru ke database dengan validasi dari StoreProjectRequest.
     * Setelah project dibuat, otomatis menambahkan creator sebagai anggota tim.
     */
    public function store(StoreProjectRequest $request)
    {
        try {
            // Mulai database transaction untuk memastikan data consistency
            DB::beginTransaction();

            // Mengambil data yang sudah divalidasi dari Request
             $validatedData = $request->validated();
            
            // Menambahkan data tambahan yang diperlukan
            $validatedData['created_by'] = Auth::id(); // ID user yang membuat
            $validatedData['deadline'] = Carbon::parse($validatedData['deadline'])->format('Y-m-d');
            
            
            
            $teamLeadId = $validatedData['teamlead_id'];
            unset($validatedData['teamlead_id']);


            // Simpan project ke database
            $project = Project::create($validatedData);

            
            // Otomatis menambahkan creator sebagai Team Lead di project
            ProjectMember::create([
                'project_id' => $project->id,
                'user_id' => $teamLeadId,
                'role' => 'team lead', // Creator otomatis jadi team lead
                'joined_at' => now()
            ]);

            // Commit transaction jika semua berhasil
            DB::commit();

            return redirect()
                ->route('projects.index')
                ->with('success', 'Project berhasil dibuat! Anda otomatis menjadi Team Lead.');

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat project: ' . $e->getMessage());
        }
    }













    /**
     * Display the specified resource.
     * 
     * Menampilkan detail lengkap dari sebuah project termasuk:
     * - Informasi project
     * - Daftar anggota tim dengan role masing-masing
     * - Statistik project (total boards, cards, dll)
     */
    public function show(Project $project)
    {
        // Load relasi yang diperlukan untuk halaman detail
        $project->load([
            'creator:id,full_name,email',
            'members' => function($query) {
                $query->with('user:id,full_name,email')->orderBy('role');
            },
            'boards' => function($query) {
                $query->withCount('cards')->orderBy('position');
            },
            'boards.cards' => function($query) {
                $query->withCount('comments')->orderBy('position');
            },
        ]);
        
        
        

        // Hitung statistik project
        $statistics = [
            'total_boards' => $project->boards->count(),
            'total_cards' => $project->boards->sum('cards_count'),
            'total_members' => $project->members->count(),
            'days_remaining' => round(Carbon::parse($project->deadline)->diffInDays(Carbon::now(), false) * -1),
        ];


        
        
        // Cek apakah user adalah anggota project
        $isMember = $project->members()->where('user_id', Auth::id())->exists();
        $userRole = $isMember ? $project->members()->where('user_id', Auth::id())->first()->role : null;


        // dd($statistics);
        return view('projects.show', compact('project', 'statistics', 'isMember', 'userRole'));
    }











    /**
     * Show the form for editing the specified resource.
     * 
     * Menampilkan form edit project. 
     * Hanya creator atau team lead yang bisa mengedit project.
     */
    public function edit(Project $project)
    {
        // Cek authorization - hanya creator atau team lead yang bisa edit
        $userRole = auth()->user()->role;

        // dd([
        //     'project_creator' => $project->created_by,
        //     'current_user' => Auth::id(),
        //     'user_role' => $userRole,
        // ]);
        
        
        if ($project->created_by != Auth::id() && $userRole !== 'admin') {
            return redirect()
                ->route('projects.show', $project)
                ->with('error', 'Anda tidak memiliki akses untuk mengedit project ini.');
        }   

        // Mengambil daftar users untuk dropdown (jika diperlukan)
        $users = User::select('id', 'full_name', 'email')
            ->orderBy('full_name')
            ->get();

        return view('projects.edit', compact('project', 'users'));
    }










    /**
     * Update the specified resource in storage.
     * 
     * Mengupdate data project dengan validasi dari UpdateProjectRequest.
     * Hanya creator atau team lead yang bisa mengupdate.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // Authorization check via Policy
        $this->authorize('update', $project);
        
        try {
            // Mulai transaction
            DB::beginTransaction();

            // Ambil data yang sudah divalidasi
            $validatedData = $request->validated();
            
            // Format deadline jika ada
            if (isset($validatedData['deadline'])) {
                $validatedData['deadline'] = Carbon::parse($validatedData['deadline'])->format('Y-m-d');
            }

            // Update project
            $project->update($validatedData);

            DB::commit();

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Project berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate project: ' . $e->getMessage());
        }
    }












    /**
     * Remove the specified resource from storage.
     * 
     * Menghapus project dari database.
     * Hanya creator project yang bisa menghapus.
     * Akan menghapus semua data terkait (cascade delete).
     */
    public function destroy(Project $project)
    {
        try {
            // Cek authorization - hanya creator yang bisa hapus
            if ($project->created_by !== Auth::id()) {
                return redirect()
                    ->route('projects.index')
                    ->with('error', 'Anda tidak memiliki akses untuk menghapus project ini.');
            }

            // Mulai transaction
            DB::beginTransaction();

            // Simpan nama project untuk pesan sukses
            $projectName = $project->name;

            // Hapus project (cascade delete akan menghapus semua data terkait)
            $project->delete();

            DB::commit();

            return redirect()
                ->route('projects.index')
                ->with('success', "Project '{$projectName}' berhasil dihapus!");

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->route('projects.index')
                ->with('error', 'Gagal menghapus project: ' . $e->getMessage());
        }
    }










    /**
     * Menampilkan project yang dibuat oleh user yang sedang login.
     * 
     * Helper method untuk menampilkan "My Projects".
     */
    public function myProjects()
    {
        $projects = Project::with(['creator:id,name,email', 'members'])
            ->where('created_by', Auth::id())
            ->withCount('members')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('projects.my-projects', compact('projects'));
    }








    /**
     * Menampilkan project dimana user adalah anggota tim.
     * 
     * Helper method untuk menampilkan project yang diikuti user.
     */
    public function joinedProjects()
    {
        $projects = Project::with(['creator:id,name,email', 'members'])
            ->whereHas('members', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->withCount('members')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('projects.joined-projects', compact('projects'));
    }
}
