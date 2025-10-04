<?php

namespace App\Policies;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * TimeLogPolicy - Authorization untuk Time Tracking Management
 * 
 * Policy ini mengatur akses ke TimeLog berdasarkan role project member:
 * - PROJECT MEMBER dengan role 'team lead', 'developer', 'designer' bisa CRUD TimeLog
 * - Time tracking untuk melacak waktu kerja pada subtask/card
 */
class TimeLogPolicy
{
    /**
     * Helper method untuk cek apakah user adalah project member yang valid
     */
    private function isValidProjectMember(User $user, TimeLog $timeLog): bool
    {
        return $timeLog->subtask->card->board->project->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['team lead', 'developer', 'designer'])
            ->exists();
    }

    /**
     * Helper method untuk cek apakah user adalah pemilik time log tersebut
     */
    private function isOwner(User $user, TimeLog $timeLog): bool
    {
        return $timeLog->user_id === $user->id;
    }

    /**
     * Determine whether the user can view any models.
     * 
     * Melihat daftar time log - project member yang valid
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
     * Melihat detail time log:
     * - Project member di project tersebut bisa lihat
     * - Atau pemilik time log bisa lihat miliknya sendiri
     */
    public function view(User $user, TimeLog $timeLog): bool
    {
        return $this->isValidProjectMember($user, $timeLog) || $this->isOwner($user, $timeLog);
    }

    /**
     * Determine whether the user can create models.
     * 
     * Membuat time log baru - project member yang valid
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
     * Update time log:
     * - Pemilik time log bisa update miliknya sendiri
     * - Team lead bisa update semua time log di project mereka
     */
    public function update(User $user, TimeLog $timeLog): bool
    {
        // Pemilik bisa update miliknya sendiri
        if ($this->isOwner($user, $timeLog)) {
            return true;
        }

        // Team lead bisa update semua time log di project mereka
        return $timeLog->subtask->card->board->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Hapus time log:
     * - Pemilik time log bisa hapus miliknya sendiri
     * - Team lead bisa hapus semua time log di project mereka
     */
    public function delete(User $user, TimeLog $timeLog): bool
    {
        // Pemilik bisa hapus miliknya sendiri
        if ($this->isOwner($user, $timeLog)) {
            return true;
        }

        // Team lead bisa hapus semua time log di project mereka
        return $timeLog->subtask->card->board->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TimeLog $timeLog): bool
    {
        return $this->isValidProjectMember($user, $timeLog);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TimeLog $timeLog): bool
    {
        return $this->isValidProjectMember($user, $timeLog);
    }
}
