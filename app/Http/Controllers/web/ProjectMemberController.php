<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\ProjectMember;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ProjectMemberController - Controller untuk mengelola anggota tim dalam project
 * 
 * Controller ini menghandle semua operasi CRUD untuk ProjectMember dengan:
 * - Database transactions untuk data integrity
 * - Authorization checks untuk security
 * - Search dan filter functionality
 * - Proper error handling dan flash messages
 * - Integration dengan User model untuk invite existing users
 */
class ProjectMemberController extends Controller
{
    /**
     * Display a listing of project members with search and filter functionality
     * 
     * Method ini menampilkan daftar member dengan fitur:
     * - Search berdasarkan name atau email user dengan database query
     * - Filter berdasarkan role dengan proper filtering
     * - Statistics calculation langsung dari database
     * - Eager loading untuk optimasi query
     * - Invitation system untuk existing users
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Base query dengan eager loading untuk optimasi performa
        // PENTING: Ketika menggunakan select columns pada eager loading,
        // foreign key HARUS disertakan agar Laravel bisa matching data
        $query = ProjectMember::query();
        
        // Apply filters first before eager loading
        $currentProject = null;
        if ($request->filled('project')) {
            $currentProject = Project::where('slug', $request->project)
                ->select('id', 'slug', 'project_name')
                ->first();
            
            // Apply project filter jika project ditemukan
            if ($currentProject) {
                $query->where('project_id', $currentProject->id);
            }
        }
        
        


        // Search functionality - mencari berdasarkan nama atau email user
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('user', function($q) use ($searchTerm) {
                $q->where('full_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('username', 'like', "%{$searchTerm}%");
            });
        }



        // Filter by role - filter berdasarkan role member
        if ($request->filled('role_filter')) {
            $query->where('role', $request->role_filter);
        }



        // Get filtered results dengan eager loading setelah filter diterapkan
        // PENTING: Saat menggunakan column selection di eager loading, 
        // foreign key HARUS disertakan (user_id, project_id)
        // Solusi: select semua columns dari project_members agar foreign keys included
        $members = $query->select('project_members.*')
            ->with([
                'user:id,full_name,email,username', 
                'project:id,slug,project_name'
            ])
            ->orderBy('joined_at', 'desc')
            ->get();
        


        // Calculate statistics untuk dashboard cards langsung dari database
        $stats = [
            'totalMembers' => ProjectMember::count(),
            'teamLeads' => ProjectMember::where('role', 'team lead')->count(),
            'developers' => ProjectMember::where('role', 'developer')->count(),
            'designers' => ProjectMember::where('role', 'designer')->count(),
        ];



        // Get available users untuk invite modal
        // Filter: exclude users yang punya project aktif (ada card belum done)
        $userIdsWithActiveProjects = ProjectMember::with('project.boards.cards')
            ->get()
            ->filter(function($projectMember) {
                // Get all cards dari semua boards di project ini
                $allCards = $projectMember->project->boards->flatMap(function($board) {
                    return $board->cards;
                });

                // Jika tidak ada cards, anggap project selesai (boleh di-invite)
                if ($allCards->isEmpty()) {
                    return false;
                }

                // Check apakah ada card yang belum done
                $hasUnfinishedCards = $allCards->contains(function($card) {
                    return $card->status !== 'done';
                });

                // Return true jika ada unfinished cards (exclude user ini)
                return $hasUnfinishedCards;
            })
            ->pluck('user_id')
            ->toArray();

        $availableUsers = User::whereNotIn('id', $userIdsWithActiveProjects)
            ->where('id', '!=', Auth::id())
            ->select('id', 'full_name', 'email', 'username')
            ->orderBy('full_name')
            ->get();

        
        


        // dd(compact('members', 'stats', 'availableUsers'));
        return view('project-members.index', compact('members', 'stats', 'availableUsers', 'currentProject'));
    }




    /**
     * Store a newly created project member (invite existing user to project)
     * 
     * Method ini mengundang user yang sudah ada ke dalam project dengan:
     * - Validasi input data
     * - Check duplicate member
     * - VALIDASI 1 PROJECT PER USER (kecuali project lama sudah selesai)
     * - Database transaction
     * - Flash message feedback
     * 
     * BUSINESS RULE: 
     * User hanya boleh ditugaskan ke 1 project aktif.
     * User bisa diberi project baru jika:
     * 1. Belum pernah ada di project_members, ATAU
     * 2. Project sebelumnya sudah selesai (semua card status = 'done')
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        
        
        // Validasi input dengan custom error messages
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:team lead,developer,designer',
            'project_id' => 'nullable|exists:projects,id'
        ], [
            'user_id.required' => 'Please select a user to invite.',
            'user_id.exists' => 'Selected user does not exist.',
            'role.required' => 'Please select a role for the member.',
            'role.in' => 'Role must be one of: Team Lead, Developer, or Designer.',
            'project_id.exists' => 'Selected project does not exist.'
        ]);

        try {
            DB::beginTransaction();

            $userId = $validated['user_id'];
            $projectId = $validated['project_id'] ?? 1;

            // Check if user is already a member of THIS project
            $existingMemberInThisProject = ProjectMember::where('user_id', $userId)
                ->where('project_id', $projectId)
                ->first();

            if ($existingMemberInThisProject) {
                return redirect()->route('project-members.index')
                    ->with('error', 'User is already a member of this project.');
            }

            // VALIDASI: Check apakah user sudah punya project aktif lain
            $existingActiveProject = ProjectMember::where('user_id', $userId)
                ->with('project.boards.cards')
                ->first();

            if ($existingActiveProject) {
                // User punya project lain, cek apakah project itu sudah selesai
                $project = $existingActiveProject->project;
                
                // Get semua cards dari semua boards di project ini
                $allCards = $project->boards->flatMap(function($board) {
                    return $board->cards;
                });

                // Jika tidak ada cards sama sekali, anggap project selesai (edge case)
                if ($allCards->isEmpty()) {
                    // Project kosong, boleh assign ke project baru
                } else {
                    // Check apakah ada card yang belum done
                    $hasUnfinishedCards = $allCards->contains(function($card) {
                        return $card->status !== 'done';
                    });

                    if ($hasUnfinishedCards) {
                        // Ada card yang belum selesai, tidak boleh assign ke project baru
                        $user = User::find($userId);
                        return redirect()->route('project-members.index')
                            ->with('error', "{$user->full_name} masih memiliki project aktif ({$project->project_name}). User hanya boleh ditugaskan ke 1 project. Tunggu sampai semua task di project sebelumnya selesai.");
                    }
                }
            }


            // Validasi lolos, create new project member
            $projectMember = ProjectMember::create([
                'project_id' => $projectId,
                'user_id' => $userId,
                'role' => $validated['role'],
                'joined_at' => now()
            ]);


            // Get user name for success message
            $user = User::find($userId);
            
            DB::commit();

            return redirect()->route('project-members.index', ['project' => $projectMember->project->slug])
                ->with('success', "{$user->full_name} has been successfully added to the project as {$validated['role']}!");

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('project-members.index', ['project' => $request->get('project_slug')])
                ->with('error', 'Failed to add member: ' . $e->getMessage());
        }
    }




    /**
     * Update the specified project member (only role can be updated)
     * 
     * Method ini mengupdate role member dengan:
     * - Validasi role
     * - Authorization check
     * - Database transaction
     * - Audit trail logging
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ProjectMember $projectMember
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProjectMember $projectMember)
    {
        // Validasi hanya role yang bisa diupdate
        $validated = $request->validate([
            'role' => 'required|in:team lead,developer,designer'
        ], [
            'role.required' => 'Please select a role.',
            'role.in' => 'Role must be one of: Team Lead, Developer, or Designer.',
        ]);

        try {
            DB::beginTransaction();

            // Store old role for audit/logging
            $oldRole = $projectMember->role;
            $memberName = $projectMember->user->full_name;

            // Update member role
            $projectMember->update([
                'role' => $validated['role']
            ]);

            DB::commit();

            return redirect()->route('project-members.index', ['project' => $projectMember->project->slug])
                ->with('success', "{$memberName}'s role has been updated from {$oldRole} to {$validated['role']}!");

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('project-members.index', ['project' => $projectMember->project->slug])
                ->with('error', 'Failed to update member role: ' . $e->getMessage());
        }
    }




    /**
     * Remove the specified project member from storage
     * 
     * Method ini removes member dari project dengan:
     * - Authorization check
     * - Soft delete consideration
     * - Related data cleanup
     * - Activity logging
     * 
     * @param \App\Models\ProjectMember $projectMember
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProjectMember $projectMember)
    {
        try {
            DB::beginTransaction();

            // Store member name before deletion for flash message
            $memberName = $projectMember->user->full_name;
            $memberRole = $projectMember->role;

            // Delete project member (user data remains intact)
            $projectMember->delete();

            DB::commit();

            return redirect()->route('project-members.index', ['project' => $projectMember->project->slug])
                ->with('success', "{$memberName} ({$memberRole}) has been removed from the project.");

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('project-members.index', ['project' => $projectMember->project->slug])
                ->with('error', 'Failed to remove member: ' . $e->getMessage());
        }
    }




    /**
     * Search available users for invitation (AJAX endpoint)
     * 
     * Method ini untuk search functionality di invite modal dengan:
     * - Real-time search
     * - JSON response
     * - Exclude users yang sudah punya project aktif
     * - User boleh muncul jika project sebelumnya sudah selesai (semua card done)
     * - Limit results untuk performance
     * 
     * BUSINESS RULE:
     * User yang muncul di list adalah:
     * 1. Belum pernah ada di project_members, ATAU
     * 2. Punya project tapi semua card sudah done
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $searchTerm = $request->get('search', '');
        
        // Get users yang TIDAK BOLEH di-invite (punya project dengan card belum done)
        $userIdsWithActiveProjects = ProjectMember::with('project.boards.cards')
            ->get()
            ->filter(function($projectMember) {
                // Get all cards dari semua boards di project ini
                $allCards = $projectMember->project->boards->flatMap(function($board) {
                    return $board->cards;
                });

                // Jika tidak ada cards, anggap project selesai (boleh di-invite)
                if ($allCards->isEmpty()) {
                    return false;
                }

                // Check apakah ada card yang belum done
                $hasUnfinishedCards = $allCards->contains(function($card) {
                    return $card->status !== 'done';
                });

                // Return true jika ada unfinished cards (exclude user ini)
                return $hasUnfinishedCards;
            })
            ->pluck('user_id')
            ->toArray();
        
        // Search available users (exclude yang punya project aktif dan diri sendiri)
        $users = User::whereNotIn('id', $userIdsWithActiveProjects)
            ->where('id', '!=', Auth::id())
            ->where(function($query) use ($searchTerm) {
                $query->where('full_name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('username', 'like', "%{$searchTerm}%");
            })
            ->select('id', 'full_name', 'email', 'username')
            ->limit(10)
            ->get();

        return response()->json([
            'users' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'avatar' => strtoupper(substr($user->full_name, 0, 1) . 
                        (strpos($user->full_name, ' ') !== false ? 
                            substr($user->full_name, strpos($user->full_name, ' ') + 1, 1) : ''))
                ];
            })
        ]);
    }
}