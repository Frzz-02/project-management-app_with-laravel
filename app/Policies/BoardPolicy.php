<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use App\Models\ProjectMember;
use Illuminate\Auth\Access\Response;

/**
 * BoardPolicy - Authorization untuk Board Management
 * 
 * Policy ini mengatur akses ke Board berdasarkan role:
 * - Admin bisa CRUD semua board
 * - User dengan role 'team lead' di project_members bisa CRUD board di project mereka
 * - Team lead yang mengatur struktur kanban board di project
 */
class BoardPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar board:
     * - Admin bisa lihat semua
     * - Team lead bisa lihat board di project mereka
     */
    public function viewAny(User $user): bool
    {
        // Admin bisa akses semua
        if ($user->role === 'admin') {
            return true;
        }
        
        // Check if user adalah team lead di minimal 1 project
        return ProjectMember::where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail board:
     * - Admin bisa lihat semua board
     * - Team lead bisa lihat board di project mereka
     * - Project member bisa lihat board di project mereka
     */
    public function view(User $user, Board $board): bool
    {
        // Admin bisa lihat semua board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Team lead atau member bisa lihat board di project mereka
        return $board->project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     * 
     * Membuat board baru:
     * - Admin bisa create board di semua project
     * - Team lead bisa create board di project mereka
     */
    public function create(User $user): bool
    {
        // Admin bisa create board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Check if user adalah team lead di minimal 1 project
        return ProjectMember::where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Update board:
     * - Admin bisa update semua board
     * - Team lead bisa update board di project mereka
     */
    public function update(User $user, Board $board): bool
    {
        // Admin bisa update semua board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Check if user adalah team lead di project ini
        return ProjectMember::where('user_id', $user->id)
            ->where('project_id', $board->project_id)
            ->where('role', 'team lead')
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus board:
     * - Admin bisa delete semua board
     * - Team lead bisa delete board di project mereka
     */
    public function delete(User $user, Board $board): bool
    {
        // Admin bisa delete semua board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Check if user adalah team lead di project ini
        return ProjectMember::where('user_id', $user->id)
            ->where('project_id', $board->project_id)
            ->where('role', 'team lead')
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Board $board): bool
    {
        // Admin bisa restore semua board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Team lead bisa restore board di project mereka
        return ProjectMember::where('user_id', $user->id)
            ->where('project_id', $board->project_id)
            ->where('role', 'team lead')
            ->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Board $board): bool
    {
        // Admin bisa force delete semua board
        if ($user->role === 'admin') {
            return true;
        }
        
        // Team lead bisa force delete board di project mereka
        return ProjectMember::where('user_id', $user->id)
            ->where('project_id', $board->project_id)
            ->where('role', 'team lead')
            ->exists();
    }
}

