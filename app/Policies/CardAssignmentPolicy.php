<?php

namespace App\Policies;

use App\Models\CardAssignment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * CardAssignmentPolicy - Authorization untuk Card Assignment Management
 * 
 * Policy ini mengatur akses ke CardAssignment berdasarkan role:
 * - Hanya TEAM LEAD yang bisa CRUD Card Assignment
 * - Project member lain bisa melihat assignment di project mereka
 * - Assignment menentukan siapa yang mengerjakan card tertentu
 */
class CardAssignmentPolicy
{
    /**
     * Helper method untuk cek apakah user adalah team lead di project
     */
    private function isTeamLeadInProject(User $user, CardAssignment $cardAssignment): bool
    {
        return $cardAssignment->card->board->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }

    /**
     * Helper method untuk cek apakah user adalah project member
     */
    private function isProjectMember(User $user, CardAssignment $cardAssignment): bool
    {
        return $cardAssignment->card->board->project->members()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Helper method untuk cek apakah user adalah assigned user
     */
    private function isAssignedUser(User $user, CardAssignment $cardAssignment): bool
    {
        return $cardAssignment->user_id === $user->id;
    }

    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar card assignment - team lead
     */
    public function viewAny(User $user): bool
    {
        // User harus team lead di minimal satu project
        return $user->projectMemberships()->where('role', 'team lead')->exists();
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail card assignment:
     * - Project member bisa lihat assignment di project mereka
     * - Assigned user bisa lihat assignment untuk dirinya
     */
    public function view(User $user, CardAssignment $cardAssignment): bool
    {
        return $this->isProjectMember($user, $cardAssignment) || 
               $this->isAssignedUser($user, $cardAssignment);
    }

    /**
     * Determine whether the user can create models.
     * 
     * Membuat card assignment baru - hanya team lead
     */
    public function create(User $user): bool
    {
        // User harus team lead di minimal satu project
        return $user->projectMemberships()->where('role', 'team lead')->exists();
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Update card assignment:
     * - Team lead bisa update semua assignment di project mereka
     * - Assigned user bisa update status assignment miliknya sendiri
     */
    public function update(User $user, CardAssignment $cardAssignment): bool
    {
        // Team lead bisa update semua assignment
        if ($this->isTeamLeadInProject($user, $cardAssignment)) {
            return true;
        }

        // Assigned user bisa update status assignment miliknya sendiri
        return $this->isAssignedUser($user, $cardAssignment);
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus card assignment - hanya team lead
     */
    public function delete(User $user, CardAssignment $cardAssignment): bool
    {
        return $this->isTeamLeadInProject($user, $cardAssignment);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CardAssignment $cardAssignment): bool
    {
        return $this->isTeamLeadInProject($user, $cardAssignment);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CardAssignment $cardAssignment): bool
    {
        return $this->isTeamLeadInProject($user, $cardAssignment);
    }
}
