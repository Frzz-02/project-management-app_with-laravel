<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * ProjectPolicy - Authorization untuk Project Management
 * 
 * Policy ini mengatur akses ke Project berdasarkan role:
 * - Hanya USER dengan role 'admin' yang bisa CRUD Project
 * - Mirip dengan JIRA dimana admin yang mengelola project
 */
class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar semua project - hanya admin
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail project tertentu:
     * - Admin bisa lihat semua project
     * - User biasa hanya bisa lihat project dimana dia adalah member
     */
    public function view(User $user, Project $project): bool
    {
        // Admin bisa lihat semua project
        if ($user->role === 'admin') {
            return true;
        }
        
        // User biasa hanya bisa lihat project dimana dia adalah member
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     * 
     * Membuat project baru - hanya admin yang bisa
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Update project - hanya admin yang bisa
     */
    public function update(User $user, Project $project): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus project - hanya admin yang bisa
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->role === 'admin';
    }
}
