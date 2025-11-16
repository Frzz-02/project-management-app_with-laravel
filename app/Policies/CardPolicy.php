<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * CardPolicy - Authorization untuk Card/Task Management
 * 
 * Policy ini mengatur akses ke Card (Tugas) berdasarkan role:
 * - Hanya PROJECT MEMBER dengan role 'team lead' yang bisa CRUD Card
 * - Mirip dengan JIRA dimana team lead yang mengelola task/issue
 */
class CardPolicy
{
    /**
     * Helper method untuk cek apakah user adalah team lead di project tertentu
     */
    private function isTeamLeadInProject(User $user, Card $card): bool
    {
        return $card->board->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }







    /**
     * Helper method untuk cek apakah user adalah member di project tertentu
     */
    private function isMemberInProject(User $user, Card $card): bool
    {
        return $card->board->project->members()
            ->where('user_id', $user->id)
            ->exists();
    }





    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar card - hanya team lead
     */
    public function viewAny(User $user): bool
    {
        // Cek apakah user adalah team lead di minimal satu project
        return $user->projectMemberships()->where('role', 'team lead')->exists();
    }





    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail card:
     * - Team lead bisa lihat card di project mereka
     * - Member project bisa lihat card (read-only)
     */
    public function view(User $user, Card $card): bool
    {
        // Member project bisa lihat card
        return $this->isMemberInProject($user, $card);
    }





    /**
     * Determine whether the user can create models.
     * 
     * Membuat card baru - hanya team lead di project tersebut
     */
    public function create(User $user): bool
    {
        // User harus memiliki role team lead di minimal satu project
        return $user->projectMemberships()->where('role', 'team lead')->exists();
    }






    /**
     * Determine whether the user can update the model.
     * 
     * Update card - hanya team lead di project tersebut
     */
    public function update(User $user, Card $card): bool
    {
        return $this->isTeamLeadInProject($user, $card);
    }






    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus card - hanya team lead di project tersebut
     */
    public function delete(User $user, Card $card): bool
    {
        return $this->isTeamLeadInProject($user, $card);
    }






    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Card $card): bool
    {
        return $this->isTeamLeadInProject($user, $card);
    }






    
    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Card $card): bool
    {
        return $this->isTeamLeadInProject($user, $card);
    }
}
