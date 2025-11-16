<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBoardRequest $request)
    {
        $this->authorize('create', Board::class);
        
        try {
            DB::beginTransaction();
            
            $validatedData = $request->validated();
            $board = Board::create($validatedData);
            
            DB::commit();
            
            // Clear cache untuk memastikan board baru muncul
            Cache::forget('project_' . $board->project_id);

            return redirect()
                ->route('projects.show', $board->project_id)
                ->with('success', 'Board created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create board: ' . $e->getMessage());
        }
    }
    
    
    
    
    

    /**
     * Display the specified resource.
     * Menampilkan halaman board dengan cards dalam layout kanban
     */
    public function show(string $id)
    {
        try {
            // Get current user info
            $currentUserId = Auth::id();
            $currentUser = Auth::user();
            
            // Eager loading board dengan cards, assignments, comments, timeLogs
            $board = Board::with([
                'project',
                'project.members.user',
                'cards' => function ($query) use ($currentUserId, $currentUser) {
                    $query->orderBy('position')->orderBy('created_at');
                    
                    // Filter cards berdasarkan role user
                    // Admin dan creator project bisa lihat semua
                    // Team Lead bisa lihat semua cards dalam project mereka
                    // Member hanya bisa lihat cards yang assigned kepada mereka
                    if ($currentUser->role === 'member') {
                        // Check apakah user adalah team lead di project ini
                        $query->whereHas('board.project.members', function ($q) use ($currentUserId) {
                            $q->where('user_id', $currentUserId)
                              ->where('role', 'team lead');
                        })
                        ->orWhereHas('assignments', function ($q) use ($currentUserId) {
                            $q->where('user_id', $currentUserId);
                        });
                    }
                },
                'cards.creator',
                'cards.subtasks',
                'cards.comments.user',
                'cards.assignments.user',
                'cards.timeLogs' // Load ALL timeLogs (filter in blade)
            ])->findOrFail($id);

            // Authorization check - apakah user adalah member dari project ini?
            if (!$board->project->members->contains('user_id', Auth::id()) && 
                $board->project->created_by !== Auth::id() && !(Auth::user()->role == 'admin') ) {
                abort(403, 'Anda tidak memiliki akses ke board ini.');
            }
            
            // Check user role in project (for view conditional logic)
            $userProjectMember = $board->project->members->firstWhere('user_id', $currentUserId);
            $userRoleInProject = $userProjectMember ? $userProjectMember->role : null;
            $isProjectCreator = $board->project->created_by === $currentUserId;
            $isAdmin = $currentUser->role === 'admin';
            $isTeamLead = $userRoleInProject === 'team lead';

            // Group cards by status untuk kanban columns
            $cardsByStatus = $board->cards->groupBy('status');

            // Statistics
            $stats = [
                'total_cards' => $board->cards->count(),
                'todo_cards' => $cardsByStatus->get('todo', collect())->count(),
                'in_progress_cards' => $cardsByStatus->get('in progress', collect())->count(),
                'review_cards' => $cardsByStatus->get('review', collect())->count(),
                'done_cards' => $cardsByStatus->get('done', collect())->count(),
                'overdue_cards' => $board->cards->filter(function ($card) {
                    return $card->due_date && $card->due_date->isPast() && $card->status !== 'done';
                })->count()
            ];

            return view('boards.show', compact(
                'board', 
                'cardsByStatus', 
                'stats',
                'userRoleInProject',
                'isProjectCreator',
                'isAdmin',
                'isTeamLead'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Board tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * 
     * Mengupdate board_name dan description saja.
     * Position dan project_id tidak bisa diubah.
     */
    public function update(UpdateBoardRequest $request, Board $board)
    {
        // dd($request->all());
        // Authorization check via Policy
        $this->authorize('update', $board);
        
        
        try {
            DB::beginTransaction();
            
            // Ambil data yang sudah divalidasi
            $validatedData = $request->validated();
            
            // Update hanya board_name dan description
            $board->update([
                'board_name' => $validatedData['board_name'],
                'description' => $validatedData['description'] ?? null,
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('boards.show', $board->id)
                ->with('success', 'Board berhasil diupdate!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate board: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * Menghapus board beserta seluruh cards dan relasi terkait.
     * Menggunakan cascade delete dari database foreign key constraints.
     */
    public function destroy(Board $board)
    {
        // Authorization check via Policy
        $this->authorize('delete', $board);
        
        try {
            DB::beginTransaction();
            
            // Simpan project_id sebelum board dihapus
            $projectId = $board->project_id;
            $boardName = $board->board_name;
            
            // Delete board (cascade akan hapus cards, subtasks, comments, dll)
            $board->delete();
            
            DB::commit();
            
            return redirect()
                ->route('projects.show', $projectId)
                ->with('success', "Board '{$boardName}' berhasil dihapus beserta semua cards dan data terkait.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus board: ' . $e->getMessage());
        }
    }



    /**
     * ====================================================================================================
     * GET BOARD MEMBERS - API Endpoint
     * ====================================================================================================
     * 
     * Mengambil daftar project members dari board yang dipilih.
     * Digunakan untuk dynamic loading saat user memilih board di add card modal.
     * 
     * @param int $id - Board ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembers($id)
    {
        try {
            $board = Board::with(['project.members.user'])->findOrFail($id);
            
            // Authorization check
            if (!$board->project->members->contains('user_id', Auth::id()) && 
                $board->project->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Format members data
            $members = $board->project->members->map(function($member) {
                return [
                    'id' => $member->id,
                    'user_id' => $member->user_id,
                    'role' => $member->role,
                    'user' => [
                        'id' => $member->user->id,
                        'username' => $member->user->username,
                        'full_name' => $member->user->full_name,
                        'email' => $member->user->email
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'members' => $members
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load members: ' . $e->getMessage()
            ], 500);
        }
    }
}
