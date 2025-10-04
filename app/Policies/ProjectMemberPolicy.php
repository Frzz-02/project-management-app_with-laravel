<?php

namespace App\Policies;

use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * ProjectMemberPolicy - Authorization untuk Project Member Management
 * 
 * Policy ini mengatur akses ke ProjectMember berdasarkan role:
 * - Hanya USER dengan role 'admin' yang bisa CRUD ProjectMember
 * - Admin yang menentukan siapa saja anggota tim di project
 */
class ProjectMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar project member - hanya admin
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail project member - hanya admin
     */
    public function view(User $user, ProjectMember $projectMember): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     * 
     * Menambahkan member baru ke project - hanya admin
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Update role member di project - hanya admin
     */
    public function update(User $user, ProjectMember $projectMember): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Remove member dari project - hanya admin
     */
    public function delete(User $user, ProjectMember $projectMember): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectMember $projectMember): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectMember $projectMember): bool
    {
        return $user->role === 'admin';
    }
}
