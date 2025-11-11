<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * BoardPolicy - Authorization untuk Board Management
 * 
 * Policy ini mengatur akses ke Board berdasarkan role:
 * - Hanya USER dengan role 'admin' yang bisa CRUD Board
 * - Admin yang mengatur struktur kanban board di project
 */
class BoardPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar board - hanya admin
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail board:
     * - Admin bisa lihat semua board
     * - Project member bisa lihat board di project mereka
     */
    public function view(User $user, Board $board): bool
    {
        // Admin bisa lihat semua board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Project member bisa lihat board di project mereka
        return $board->project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     * 
     * Membuat board baru - hanya admin
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Update board:
     * - Admin bisa update semua board
     * - Team lead dari project yang bersangkutan bisa update board
     */
    public function update(User $user, Board $board): bool
    {
        // Admin bisa update semua board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Team lead dari project bisa update board
        if ($board->project->members->contains('user_id', $user->id)) {
            $projectMember = $user->projectMemberships->firstWhere('project_id', $board->project_id);
            if ($projectMember && $projectMember->role === 'team lead') {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus board:
     * - Admin bisa hapus semua board
     * - Team lead dari project yang bersangkutan bisa hapus board
     */
    public function delete(User $user, Board $board): bool
    {
        // Admin bisa hapus semua board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Team lead dari project bisa hapus board
        if ($board->project->members->contains('user_id', $user->id)) {
            $projectMember = $user->projectMemberships->firstWhere('project_id', $board->project_id);
            if ($projectMember && $projectMember->role === 'team lead') {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Board $board): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Board $board): bool
    {
        return $user->role === 'admin';
    }
}
