<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * CommentPolicy - Authorization untuk Comment Management
 * 
 * Policy ini mengatur akses ke Comment berdasarkan project membership:
 * - Semua PROJECT MEMBER bisa CRUD Comment pada card/subtask di project mereka
 * - User hanya bisa edit/hapus comment yang mereka buat sendiri
 */
class CommentPolicy
{
    /**
     * Helper method untuk cek apakah user adalah project member
     */
    private function isProjectMember(User $user, Comment $comment): bool
    {
        // Cek melalui card jika comment di card
        if ($comment->card_id) {
            return $comment->card->board->project->members()
                ->where('user_id', $user->id)
                ->exists();
        }
        
        // Cek melalui subtask jika comment di subtask
        if ($comment->subtask_id) {
            return $comment->subtask->card->board->project->members()
                ->where('user_id', $user->id)
                ->exists();
        }
        
        return false;
    }

    /**
     * Helper method untuk cek apakah user adalah pemilik comment
     */
    private function isOwner(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar comment - semua project member
     */
    public function viewAny(User $user): bool
    {
        // User harus menjadi project member di minimal satu project
        return $user->projectMemberships()->exists();
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Melihat detail comment - project member di project tersebut
     */
    public function view(User $user, Comment $comment): bool
    {
        return $this->isProjectMember($user, $comment);
    }

    /**
     * Determine whether the user can create models.
     * 
     * Membuat comment baru - semua project member
     */
    public function create(User $user): bool
    {
        // User harus menjadi project member di minimal satu project
        return $user->projectMemberships()->exists();
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Update comment:
     * - Pemilik comment bisa edit miliknya sendiri
     * - Team lead bisa edit semua comment di project mereka
     */
    public function update(User $user, Comment $comment): bool
    {
        // Pemilik bisa edit miliknya sendiri
        if ($this->isOwner($user, $comment)) {
            return true;
        }

        // Team lead bisa edit semua comment di project mereka
        if ($comment->card_id) {
            return $comment->card->board->project->members()
                ->where('user_id', $user->id)
                ->where('role', 'team lead')
                ->exists();
        }
        
        if ($comment->subtask_id) {
            return $comment->subtask->card->board->project->members()
                ->where('user_id', $user->id)
                ->where('role', 'team lead')
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus comment:
     * - Pemilik comment bisa hapus miliknya sendiri
     * - Team lead bisa hapus semua comment di project mereka
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Pemilik bisa hapus miliknya sendiri
        if ($this->isOwner($user, $comment)) {
            return true;
        }

        // Team lead bisa hapus semua comment di project mereka
        if ($comment->card_id) {
            return $comment->card->board->project->members()
                ->where('user_id', $user->id)
                ->where('role', 'team lead')
                ->exists();
        }
        
        if ($comment->subtask_id) {
            return $comment->subtask->card->board->project->members()
                ->where('user_id', $user->id)
                ->where('role', 'team lead')
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $this->isProjectMember($user, $comment);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return $this->isProjectMember($user, $comment);
    }
}
