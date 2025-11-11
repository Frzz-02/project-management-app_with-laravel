<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * CardPolicy - Authorization untuk Card/Task Management
 * 
 * Policy ini mengatur akses ke Card (Tugas) berdasarkan role:
 * - ADMIN (role 'admin' di tabel users) memiliki akses penuh ke semua card
 * - PROJECT MEMBER dengan role 'team lead' bisa CRUD card di project mereka
 * - PROJECT MEMBER dengan role 'designer'/'developer' hanya bisa VIEW card
 * 
 * Authorization Hierarchy:
 * 1. Admin → Full access (CRUD semua card)
 * 2. Team Lead → CRUD card di project mereka
 * 3. Designer/Developer → Read-only access
 */
class CardPolicy
{
    /**
     * Helper method untuk cek apakah user adalah Admin
     * Admin memiliki akses penuh ke semua resource
     */
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

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
     * Melihat daftar card:
     * - Admin bisa lihat semua card
     * - Team lead bisa lihat card di project mereka
     * - Member bisa lihat card di project mereka
     */
    public function viewAny(User $user): bool
    {
        // Admin always has access
        if ($this->isAdmin($user)) {
            return true;
        }
        
        // Cek apakah user adalah member di minimal satu project
        return $user->projectMemberships()->exists();
    }





    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail card:
     * - Admin bisa lihat semua card
     * - Member project bisa lihat card di project mereka (read-only)
     */
    public function view(User $user, Card $card): bool
    {
        // Admin always has access
        if ($this->isAdmin($user)) {
            return true;
        }
        
        // Member project bisa lihat card
        return $this->isMemberInProject($user, $card);
    }





    /**
     * Determine whether the user can create models.
     * 
     * Membuat card baru:
     * - Admin bisa create card di semua project
     * - Team lead bisa create card di project mereka
     */
    public function create(User $user): bool
    {
        // Admin always has access
        if ($this->isAdmin($user)) {
            return true;
        }
        
        // User harus memiliki role team lead di minimal satu project
        return $user->projectMemberships()->where('role', 'team lead')->exists();
    }






    /**
     * Determine whether the user can update the model.
     * 
     * Update card:
     * - Admin bisa update semua card
     * - Team lead bisa update card di project mereka
     * - Designer/Developer TIDAK bisa update
     */
    public function update(User $user, Card $card): bool
    {
        // Admin always has access
        if ($this->isAdmin($user)) {
            return true;
        }
        
        // Team lead bisa update card di project mereka
        return $this->isTeamLeadInProject($user, $card);
    }






    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus card:
     * - Admin bisa delete semua card
     * - Team lead bisa delete card di project mereka
     * - Designer/Developer TIDAK bisa delete
     */
    public function delete(User $user, Card $card): bool
    {
        // Admin always has access
        if ($this->isAdmin($user)) {
            return true;
        }
        
        // Team lead bisa delete card di project mereka
        return $this->isTeamLeadInProject($user, $card);
    }






    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Card $card): bool
    {
        // Admin always has access
        if ($this->isAdmin($user)) {
            return true;
        }
        
        return $this->isTeamLeadInProject($user, $card);
    }






    
    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Card $card): bool
    {
        // Admin always has access
        if ($this->isAdmin($user)) {
            return true;
        }
        
        return $this->isTeamLeadInProject($user, $card);
    }
}
