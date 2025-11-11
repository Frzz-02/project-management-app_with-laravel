<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Subtask;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * SubtaskController
 * 
 * Controller untuk mengelola CRUD operations pada subtasks.
 * Subtask adalah sub-tugas yang ada dalam sebuah card (mirip Jira).
 * 
 * Authorization:
 * - Hanya Team Lead dan Project Member yang bisa manage subtasks
 * - Creator card atau Team Lead punya full access
 * 
 * @package App\Http\Controllers\web
 */
class SubtaskController extends Controller
{
    /**
     * Store a newly created subtask in storage.
     * 
     * Membuat subtask baru untuk card:
     * 1. Validasi input data
     * 2. Cek authorization (team lead atau member)
     * 3. Hitung position terbaru
     * 4. Create subtask dengan status default 'to do'
     * 
     * @param Request $request HTTP request dengan data subtask
     * @return \Illuminate\Http\RedirectResponse Redirect kembali ke card.show
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'card_id' => 'required|exists:cards,id',
            'subtask_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99'
        ]);

        try {
            // Ambil card untuk cek authorization
            $card = Card::with('board.project.members')->findOrFail($validatedData['card_id']);
            
            // Authorization check - hanya team lead atau member yang bisa create subtask
            $currentUser = Auth::user();
            $projectMember = $card->board->project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember || !in_array($projectMember->role, ['team lead', 'developer', "designer"])) {
                abort(403, 'Anda tidak memiliki izin untuk menambahkan subtask.');
            }

            // Hitung position terbaru untuk subtask dalam card
            $lastPosition = Subtask::where('card_id', $validatedData['card_id'])->max('position') ?? 0;

            // Create subtask baru
            Subtask::create([
                'card_id' => $validatedData['card_id'],
                'subtask_name' => $validatedData['subtask_name'],
                'description' => $validatedData['description'],
                'status' => 'to do', // Default status
                'estimated_hours' => $validatedData['estimated_hours'],
                'actual_hours' => 0.00,
                'position' => $lastPosition + 1,
            ]);

            // Check auto-update card status setelah create subtask
            $this->autoUpdateCardStatus($card);

            return redirect()->route('cards.show', $card)
                ->with('success', 'Subtask berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan subtask: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified subtask in storage.
     * 
     * Mengupdate data subtask (KECUALI status):
     * 1. Validasi input data
     * 2. Cek authorization
     * 3. Update field yang diizinkan (subtask_name, description, hours)
     * 
     * PENTING: Status TIDAK diupdate di method ini.
     * Gunakan updateStatus() untuk mengubah status via dropdown.
     * 
     * @param Request $request HTTP request dengan data subtask
     * @param Subtask $subtask Instance subtask yang akan diupdate
     * @return \Illuminate\Http\RedirectResponse Redirect kembali ke card.show
     */
    public function update(Request $request, Subtask $subtask)
    {
        // Validasi input (status dihapus - diupdate terpisah via dropdown)
        $validatedData = $request->validate([
            'subtask_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99',
            'actual_hours' => 'nullable|numeric|min:0|max:999.99'
        ]);

        try {
            // Authorization check
            $card = $subtask->card;
            $currentUser = Auth::user();
            $projectMember = $card->board->project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember || !in_array($projectMember->role, ['team lead', 'member'])) {
                abort(403, 'Anda tidak memiliki izin untuk mengupdate subtask.');
            }

            // Update subtask data (TANPA status)
            $subtask->update([
                'subtask_name' => $validatedData['subtask_name'],
                'description' => $validatedData['description'],
                'estimated_hours' => $validatedData['estimated_hours'],
                'actual_hours' => $validatedData['actual_hours']
            ]);

            return redirect()->route('cards.show', $card)
                ->with('success', 'Subtask berhasil diupdate.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate subtask: ' . $e->getMessage());
        }
    }

    /**
     * Update status of subtask (quick action).
     * 
     * Mengupdate status subtask secara cepat (toggle done/to do):
     * - Digunakan untuk "Mark as Done" atau "Mark as To Do"
     * - Tidak mengubah field lain
     * - **AUTO-UPDATE CARD STATUS**: Jika semua subtask done → card status menjadi "review"
     * 
     * @param Request $request HTTP request dengan status baru
     * @param Subtask $subtask Instance subtask yang akan diupdate
     * @return \Illuminate\Http\RedirectResponse Redirect kembali ke card.show
     */
    public function updateStatus(Request $request, Subtask $subtask)
    {
        // Validasi status
        $validatedData = $request->validate([
            'status' => 'required|in:to do,in progress,done'
        ]);

        try {
            // Authorization check
            $card = $subtask->card;
            $currentUser = Auth::user();
            $projectMember = $card->board->project->members->where('user_id', $currentUser->id)->first();

            if (!$projectMember || !in_array($projectMember->role, ['developer', 'designer'])) {
                abort(403, 'Anda tidak memiliki izin untuk mengubah status subtask.');
            }

            // Update hanya status
            $subtask->update([
                'status' => $validatedData['status']
            ]);

            // AUTO-UPDATE CARD STATUS: Check apakah semua subtask done
            $this->autoUpdateCardStatus($card);

            return redirect()->route('cards.show', $card)
                ->with('success', 'Status subtask berhasil diubah.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Auto-update card status based on subtasks completion.
     * 
     * Logic:
     * 1. Jika card punya subtasks DAN semua subtasks status = 'done'
     *    → Update card status menjadi 'review'
     * 
     * 2. Jika card tidak punya subtasks
     *    → Tidak ada perubahan status (card diatur manual)
     * 
     * 3. Jika ada subtask yang belum 'done'
     *    → Tidak ada perubahan status
     * 
     * @param Card $card Instance card yang akan dicek
     * @return void
     */
    private function autoUpdateCardStatus(Card $card)
    {
        // Reload subtasks untuk data terbaru
        $card->load('subtasks');

        // Cek apakah card punya subtasks
        if ($card->subtasks->count() === 0) {
            // Tidak ada subtasks, skip auto-update
            return;
        }

        // Cek apakah SEMUA subtasks statusnya 'done'
        $allSubtasksDone = $card->subtasks->every(function ($subtask) {
            return $subtask->status === 'done';
        });

        // Jika semua done DAN card status bukan 'done' atau 'review'
        // → Update card status ke 'review'
        if ($allSubtasksDone && !in_array($card->status, ['review', 'done'])) {
            $card->update([
                'status' => 'review'
            ]);

            // Optional: Log untuk debugging
            Log::info("Card #{$card->id} auto-updated to 'review' - all subtasks completed");
        }
    }

    /**
     * Remove the specified subtask from storage.
     * 
     * Menghapus subtask dari database:
     * - Menghapus subtask beserta relasinya (comments akan cascade delete)
     * - Authorization check untuk memastikan user punya akses
     * 
     * @param Subtask $subtask Instance subtask yang akan dihapus
     * @return \Illuminate\Http\RedirectResponse Redirect kembali ke card.show
     */
    public function destroy(Subtask $subtask)
    {
        try {
            // Simpan card untuk redirect
            $card = $subtask->card;
            
            // Authorization check
            $currentUser = Auth::user();
            $projectMember = $card->board->project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember || !in_array($projectMember->role, ['team lead', 'member'])) {
                abort(403, 'Anda tidak memiliki izin untuk menghapus subtask.');
            }

            // Hapus subtask
            // Related comments akan terhapus otomatis (cascade delete)
            $subtask->delete();

            // Check auto-update card status setelah delete subtask
            $this->autoUpdateCardStatus($card);

            return redirect()->route('cards.show', $card)
                ->with('success', 'Subtask berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus subtask: ' . $e->getMessage());
        }
    }
}
