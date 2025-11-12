<?php

namespace App\Policies;

use App\Models\CardReview;
use App\Models\Card;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * CardReviewPolicy - Authorization untuk Card Review Management
 * 
 * Policy ini mengatur akses ke CardReview berdasarkan role:
 * - Admin bisa melakukan semua operasi
 * - Team Lead bisa approve/reject cards di project mereka
 */
class CardReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar reviews - Admin dan Team Lead
     */
    public function viewAny(User $user): bool
    {
        // Admin bisa lihat semua reviews
        if ($user->role === 'admin') {
            return true;
        }
        
        // Team Lead bisa lihat reviews di project mereka
        return $user->projectMemberships()->where('role', 'team lead')->exists();
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail review - Admin, Team Lead, atau yang melakukan review
     */
    public function view(User $user, CardReview $cardReview): bool
    {
        // Admin bisa lihat semua reviews
        if ($user->role === 'admin') {
            return true;
        }
        
        // User yang melakukan review bisa lihat review-nya
        if ($cardReview->reviewed_by === $user->id) {
            return true;
        }
        
        // Team Lead di project yang sama bisa lihat
        $card = $cardReview->card;
        if ($card->board->project->members->contains('user_id', $user->id)) {
            $projectMember = $user->projectMemberships->firstWhere('project_id', $card->board->project_id);
            if ($projectMember && $projectMember->role === 'team lead') {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models (approve/reject card).
     * 
     * Membuat review - Admin dan Team Lead di project terkait
     */
    public function create(User $user): bool
    {
        // Admin bisa create review
        if ($user->role === 'admin') {
            return true;
        }
        
        // Team Lead bisa create review
        return $user->projectMemberships()->where('role', 'team lead')->exists();
    }

    /**
     * Cek apakah user bisa review card tertentu
     */
    public function reviewCard(User $user, Card $card): bool
    {
        // Admin bisa review semua cards
        if ($user->role === 'admin') {
            return true;
        }
        
        // Team Lead di project yang sama bisa review
        if ($card->board->project->members->contains('user_id', $user->id)) {
            $projectMember = $user->projectMemberships->firstWhere('project_id', $card->board->project_id);
            if ($projectMember && $projectMember->role === 'team lead') {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Update review - Hanya yang membuat review atau Admin
     */
    public function update(User $user, CardReview $cardReview): bool
    {
        // Admin bisa update semua reviews
        if ($user->role === 'admin') {
            return true;
        }
        
        // User yang membuat review bisa update (dalam waktu tertentu)
        return $cardReview->reviewed_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Delete review - Hanya Admin
     */
    public function delete(User $user, CardReview $cardReview): bool
    {
        // Hanya Admin yang bisa delete review history
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CardReview $cardReview): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CardReview $cardReview): bool
    {
        return $user->role === 'admin';
    }
}
