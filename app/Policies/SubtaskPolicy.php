<?php

namespace App\Policies;

use App\Models\Subtask;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * SubtaskPolicy - Authorization untuk Subtask Management
 * 
 * Policy ini mengatur akses ke Subtask berdasarkan role project member:
 * - PROJECT MEMBER dengan role 'team lead', 'developer', 'designer' bisa CRUD Subtask
 * - Subtask adalah bagian dari card, jadi semua project member bisa mengelolanya
 */
class SubtaskPolicy
{
    /**
     * Helper method untuk cek apakah user adalah project member yang valid
     */
    private function isValidProjectMember(User $user, Subtask $subtask): bool
    {
        return $subtask->card->board->project->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['team lead', 'developer', 'designer'])
            ->exists();
    }

    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar subtask - project member yang valid
     */
    public function viewAny(User $user): bool
    {
        // Cek apakah user adalah project member dengan role yang valid
        return $user->projectMemberships()
            ->whereIn('role', ['team lead', 'developer', 'designer'])
            ->exists();
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail subtask - project member di project tersebut
     */
    public function view(User $user, Subtask $subtask): bool
    {
        return $this->isValidProjectMember($user, $subtask);
    }

    /**
     * Determine whether the user can create models.
     * 
     * Membuat subtask baru - project member yang valid
     */
    public function create(User $user): bool
    {
        // User harus memiliki role yang valid di minimal satu project
        return $user->projectMemberships()
            ->whereIn('role', ['team lead', 'developer', 'designer'])
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Update subtask - project member yang valid di project tersebut
     */
    public function update(User $user, Subtask $subtask): bool
    {
        return $this->isValidProjectMember($user, $subtask);
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus subtask - project member yang valid di project tersebut
     */
    public function delete(User $user, Subtask $subtask): bool
    {
        return $this->isValidProjectMember($user, $subtask);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Subtask $subtask): bool
    {
        return $this->isValidProjectMember($user, $subtask);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Subtask $subtask): bool
    {
        return $this->isValidProjectMember($user, $subtask);
    }
}
