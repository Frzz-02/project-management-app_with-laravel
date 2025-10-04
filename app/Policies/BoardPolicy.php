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
     * Update board - hanya admin
     */
    public function update(User $user, Board $board): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus board - hanya admin
     */
    public function delete(User $user, Board $board): bool
    {
        return $user->role === 'admin';
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
