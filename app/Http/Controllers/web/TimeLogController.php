<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\TimeLog;
use App\Models\Card;
use App\Models\Subtask;
use App\Models\CardAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * TimeLogController
 * 
 * Controller ini mengelola fitur time tracking untuk card dan subtask.
 * Fitur utama:
 * - Start/Stop tracking waktu kerja
 * - Perhitungan durasi otomatis
 * - History time logs
 * - Total waktu per card/subtask
 * - Authorization untuk user yang login
 */
class TimeLogController extends Controller
{
    /**
     * ====================================
     * START TIME TRACKING
     * ====================================
     * 
     * Method ini digunakan untuk memulai tracking waktu kerja.
     * 
     * Alur kerja:
     * 1. Validasi input (card_id atau subtask_id harus ada)
     * 2. Cek apakah user sudah punya timer yang sedang berjalan
     * 3. Cek authorization (user harus member dari project)
     * 4. Simpan time log baru dengan start_time = sekarang
     * 5. Redirect kembali dengan success message
     * 
     * @param Request $request - HTTP request dengan data card_id/subtask_id dan description
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startTracking(Request $request)
    {
        // Validasi input
        // card_id ATAU subtask_id harus ada (salah satu)
        $validatedData = $request->validate([
            'card_id' => 'nullable|exists:cards,id',
            'subtask_id' => 'nullable|exists:subtasks,id',
            'description' => 'nullable|string|max:1000'
        ]);



        // Bersihkan empty string menjadi null
        $validatedData['card_id'] = !empty($validatedData['card_id']) ? $validatedData['card_id'] : null;
        $validatedData['subtask_id'] = !empty($validatedData['subtask_id']) ? $validatedData['subtask_id'] : null;



        // Pastikan minimal ada card_id atau subtask_id
        if (empty($validatedData['card_id']) && empty($validatedData['subtask_id'])) {
            return redirect()->back()->with('error', 'Card ID atau Subtask ID harus diisi.');
        }



        try {
            $currentUser = Auth::user();


            
            // ====================================
            // CONCURRENT TRACKING VALIDATION
            // ====================================
            // Rules untuk concurrent tracking:
            // 1. User TIDAK BISA start tracking yang sama 2x (same card/subtask)
            // 2. User BISA track 1 card + multiple subtasks dari card yang sama
            // 3. User TIDAK BISA track 2 cards berbeda bersamaan
            // ====================================



            // Rule 1: Check apakah card/subtask ini sudah ditracking
            $duplicateTracking = TimeLog::where('user_id', $currentUser->id)
                ->whereNull('end_time')
                ->where(function($query) use ($validatedData) {
                    if (!empty($validatedData['card_id']) && empty($validatedData['subtask_id'])) {
                        // Tracking card, cek apakah card ini sudah ditracking
                        $query->where('card_id', $validatedData['card_id'])
                              ->whereNull('subtask_id');
                    } elseif (!empty($validatedData['subtask_id'])) {
                        // Tracking subtask, cek apakah subtask ini sudah ditracking
                        $query->where('subtask_id', $validatedData['subtask_id']);
                    }
                })
                ->first();

            if ($duplicateTracking) {
                $target = !empty($validatedData['subtask_id']) ? 'subtask' : 'card';
                return redirect()->back()->with('error', "Anda sudah memiliki timer yang sedang berjalan untuk {$target} ini.");
            }



            // Rule 2 & 3: Jika tracking card, cek apakah ada card lain yang sedang ditracking
            if (empty($validatedData['subtask_id'])) {
                // User mau start tracking card (bukan subtask)
                $otherCardTracking = TimeLog::where('user_id', $currentUser->id)
                    ->whereNull('end_time')
                    ->whereNull('subtask_id') // Hanya check card tracking (bukan subtask)
                    ->where('card_id', '!=', $validatedData['card_id'])
                    ->first();

                if ($otherCardTracking) {
                    $otherCard = Card::find($otherCardTracking->card_id);
                    return redirect()->back()->with('error', 
                        "Anda masih memiliki timer card lain yang sedang berjalan: \"{$otherCard->card_title}\". Harap stop timer tersebut terlebih dahulu.");
                }
            }



            // Rule 2: Jika tracking subtask, pastikan subtask ini belong to card yang sedang ditracking (opsional)
            // Implementasi: Allow tracking subtask dari card manapun, tapi auto-set card_id
            // Ini sudah dihandle di logic berikutnya



            // Dapatkan card untuk authorization check
            $card = null;
            if (!empty($validatedData['card_id'])) {
                $card = Card::findOrFail($validatedData['card_id']);
            } elseif (!empty($validatedData['subtask_id'])) {
                $subtask = Subtask::findOrFail($validatedData['subtask_id']);
                $card = $subtask->card;
                $validatedData['card_id'] = $card->id; // Set card_id juga
            }



            // Authorization check - user harus member dari project
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember && $project->created_by !== $currentUser->id) {
                abort(403, 'Anda tidak memiliki izin untuk tracking waktu di card ini.');
            }



            // ====================================
            // SUBTASK TRACKING PREREQUISITE CHECK
            // ====================================
            // Rule: Subtask tracking HANYA bisa di-start jika card-nya sudah di-tracking
            // Ini untuk memastikan work flow yang logis: card dulu, baru subtask
            if (!empty($validatedData['subtask_id'])) {
                // User mau start subtask tracking
                // Cek apakah card ini sedang di-tracking oleh user yang sama
                $cardTracking = TimeLog::where('card_id', $validatedData['card_id'])
                    ->where('user_id', $currentUser->id)
                    ->whereNull('subtask_id')  // Hanya card tracking (bukan subtask)
                    ->whereNull('end_time')     // Yang masih ongoing
                    ->first();

                if (!$cardTracking) {
                    return redirect()->back()->with('error', 
                        'Anda harus memulai tracking card terlebih dahulu sebelum tracking subtask. Silakan start tracking pada card ini dulu.');
                }

                Log::info('Subtask tracking allowed - card tracking active', [
                    'card_id' => $validatedData['card_id'],
                    'subtask_id' => $validatedData['subtask_id'],
                    'card_tracking_id' => $cardTracking->id
                ]);
            }



            // Buat time log baru dengan start_time = sekarang (timezone Asia/Jakarta)
            $timeLog = TimeLog::create([
                'card_id' => $validatedData['card_id'],
                'subtask_id' => $validatedData['subtask_id'],
                'user_id' => $currentUser->id,
                'start_time' => Carbon::now('Asia/Jakarta'),
                'end_time' => null, // Masih berjalan
                'duration_minutes' => 0,
                'description' => $validatedData['description'] ?? null
            ]);



            // AUTO-UPDATE STATUS KE "IN PROGRESS"
            // Logic: 
            // - Jika hanya card_id (subtask_id null) -> update status card ke "in progress"
            // - Jika card_id dan subtask_id keduanya ada -> update status subtask ke "in progress" saja
            if (!empty($validatedData['subtask_id'])) {
                // Ada subtask, update status subtask ke "in progress"
                $subtask = Subtask::find($validatedData['subtask_id']);
                if ($subtask && $subtask->status !== 'done') {
                    $subtask->update(['status' => 'in progress']);
                }
            } elseif (!empty($validatedData['card_id'])) {
                // Hanya card (tanpa subtask), update status card ke "in progress"
                if ($card->status !== 'done' && $card->status !== 'review') {
                    $card->update(['status' => 'in progress']);
                }
            }



            // UPDATE STARTED_AT DI CARD_ASSIGNMENTS
            // Hanya update jika ini adalah pertama kali user mulai mengerjakan card ini
            // (started_at masih null)
            $cardAssignment = CardAssignment::where('card_id', $validatedData['card_id'])
                ->where('user_id', $currentUser->id)
                ->first();

            if ($cardAssignment && is_null($cardAssignment->started_at)) {
                // Pertama kali start tracking untuk card ini
                $cardAssignment->update([
                    'started_at' => Carbon::now('Asia/Jakarta'),
                    'assignment_status' => 'in progress'
                ]);
            }



            // Log untuk debugging (hapus setelah testing)
            Log::info('Time tracking started', [
                'time_log_id' => $timeLog->id,
                'card_id' => $timeLog->card_id,
                'subtask_id' => $timeLog->subtask_id,
                'user_id' => $timeLog->user_id,
                'start_time' => $timeLog->start_time
            ]);


            
            return redirect()->back()->with('success', 'Time tracking dimulai!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memulai time tracking: ' . $e->getMessage());
        }
    }



    /**
     * ====================================
     * STOP TIME TRACKING
     * ====================================
     * 
     * Method ini digunakan untuk menghentikan tracking waktu kerja.
     * 
     * Alur kerja:
     * 1. Cari time log berdasarkan log_id
     * 2. Validasi bahwa log tersebut milik user yang login
     * 3. Validasi bahwa timer masih berjalan (end_time masih null)
     * 4. CEK SUBTASK TRACKING - Jika ada subtask tracking yang masih jalan, stop otomatis
     * 5. Set end_time = sekarang (Carbon::now())
     * 6. Hitung duration_minutes otomatis berdasarkan selisih start_time dan end_time
     * 7. Update description jika ada
     * 8. Redirect kembali dengan success message dan durasi formatted
     * 
     * Catatan:
     * - end_time SELALU lebih baru dari start_time karena menggunakan Carbon::now()
     * - Durasi dihitung dalam menit kemudian di-format ke jam:menit
     * - JIKA ada subtask tracking yang masih jalan, akan di-stop otomatis sebelum stop card tracking
     * 
     * @param Request $request - HTTP request dengan optional description
     * @param TimeLog $timeLog - Instance time log yang akan di-stop
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopTracking(Request $request, TimeLog $timeLog)
    {
        // Validasi input
        $validatedData = $request->validate([
            'description' => 'nullable|string|max:1000'
        ]);



        try {
            $currentUser = Auth::user();



            // Authorization check - hanya user yang memulai timer yang bisa stop
            if ($timeLog->user_id !== $currentUser->id) {
                abort(403, 'Anda tidak memiliki izin untuk menghentikan time log ini.');
            }



            // Validasi bahwa timer masih berjalan
            if (!$timeLog->isOngoing()) {
                return redirect()->back()->with('error', 'Time log ini sudah dihentikan sebelumnya.');
            }



            // ====================================
            // AUTO-STOP SUBTASK TRACKING (CASCADE)
            // ====================================
            // HANYA untuk CARD tracking (bukan subtask tracking)
            // Jika user stop card tracking, maka semua subtask tracking dari card tersebut
            // yang masih berjalan akan di-stop otomatis (cascade)
            //
            // JIKA user stop subtask tracking, TIDAK cascade - hanya stop subtask itu saja
            $stoppedSubtasksCount = 0;
            $endTime = Carbon::now('Asia/Jakarta');

            if (is_null($timeLog->subtask_id)) {
                // Ini adalah CARD tracking (subtask_id null)
                // Cari semua subtask tracking yang masih berjalan untuk card ini
                $ongoingSubtaskTimeLogs = TimeLog::where('card_id', $timeLog->card_id)
                    ->whereNotNull('subtask_id')  // Hanya subtask tracking
                    ->whereNull('end_time')        // Yang masih berjalan
                    ->where('user_id', $currentUser->id)
                    ->get();

                foreach ($ongoingSubtaskTimeLogs as $subtaskLog) {
                    // Stop subtask tracking dengan end_time yang sama
                    $subtaskDuration = $subtaskLog->start_time->diffInMinutes($endTime);
                    
                    $subtaskLog->update([
                        'end_time' => $endTime,
                        'duration_minutes' => $subtaskDuration,
                    ]);

                    $stoppedSubtasksCount++;
                    
                    Log::info('Subtask tracking auto-stopped (cascade from card)', [
                        'subtask_time_log_id' => $subtaskLog->id,
                        'subtask_id' => $subtaskLog->subtask_id,
                        'duration_minutes' => $subtaskDuration
                    ]);
                }
            } else {
                // Ini adalah SUBTASK tracking (subtask_id not null)
                // TIDAK ada cascade - hanya stop subtask ini saja
                Log::info('Stopping subtask tracking (no cascade)', [
                    'subtask_id' => $timeLog->subtask_id
                ]);
            }



            // Set end_time dan hitung durasi (menggunakan timezone Asia/Jakarta)
            // Note: $endTime sudah di-define di atas



            // Hitung durasi dalam menit
            $durationMinutes = $timeLog->start_time->diffInMinutes($endTime);



            // Update time log
            $timeLog->update([
                'end_time' => $endTime,
                'duration_minutes' => $durationMinutes,
                'description' => $validatedData['description'] ?? $timeLog->description
            ]);



            // AUTO-UPDATE STATUS SETELAH STOP TRACKING
            // CATATAN: Status tetap "in progress" setelah stop tracking
            // User harus manual ubah status via endpoint lain jika ingin ubah ke done/review
            // 
            // SUBTASK: Bisa langsung diubah ke "done" jika user mau (manual via endpoint lain)
            // CARD: Hanya bisa diubah ke "review" jika SEMUA subtask sudah "done" (validasi di endpoint update card status)
            //
            // Tidak ada perubahan status otomatis di sini - tetap "in progress"



            // UPDATE ACTUAL_HOURS DI CARDS
            // Hitung total waktu dari semua time logs yang sudah selesai untuk card ini
            $totalMinutes = TimeLog::where('card_id', $timeLog->card_id)
                ->whereNotNull('end_time')
                ->sum('duration_minutes');

            // Convert minutes ke hours (decimal)
            $actualHours = round($totalMinutes / 60, 2);

            // Update actual_hours di card
            if ($timeLog->card) {
                $timeLog->card->update([
                    'actual_hours' => $actualHours
                ]);
            }



            // UPDATE COMPLETED_AT DI CARD_ASSIGNMENTS
            // Update completed_at dan assignment_status ke 'completed'
            $cardAssignment = CardAssignment::where('card_id', $timeLog->card_id)
                ->where('user_id', $currentUser->id)
                ->first();

            if ($cardAssignment) {
                $cardAssignment->update([
                    'completed_at' => Carbon::now('Asia/Jakarta'),
                    'assignment_status' => 'completed'
                ]);
            }



            // Format durasi untuk ditampilkan
            $hours = intval($durationMinutes / 60);
            $minutes = $durationMinutes % 60;
            $formattedDuration = sprintf('%d jam %d menit', $hours, $minutes);



            // Success message dengan info subtask yang di-stop (jika ada)
            $successMessage = "Time tracking dihentikan! Durasi: {$formattedDuration}";
            if ($stoppedSubtasksCount > 0) {
                $successMessage .= " (Otomatis menghentikan {$stoppedSubtasksCount} subtask tracking)";
            }

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghentikan time tracking: ' . $e->getMessage());
        }
    }



    /**
     * ====================================
     * UPDATE TIME LOG
     * ====================================
     * 
     * Method ini digunakan untuk update description atau data time log yang sudah selesai.
     * TIDAK BISA digunakan untuk update time log yang masih berjalan (end_time null).
     * 
     * @param Request $request - HTTP request dengan data yang akan diupdate
     * @param TimeLog $timeLog - Instance time log yang akan diupdate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TimeLog $timeLog)
    {
        // Validasi input
        $validatedData = $request->validate([
            'description' => 'nullable|string|max:1000'
        ]);



        try {
            $currentUser = Auth::user();



            // Authorization check
            if ($timeLog->user_id !== $currentUser->id) {
                abort(403, 'Anda tidak memiliki izin untuk mengupdate time log ini.');
            }



            // Validasi bahwa time log sudah selesai (tidak bisa edit yang sedang berjalan)
            if ($timeLog->isOngoing()) {
                return redirect()->back()->with('error', 'Tidak bisa update time log yang masih berjalan. Harap stop terlebih dahulu.');
            }



            // Update data
            $timeLog->update([
                'description' => $validatedData['description']
            ]);



            return redirect()->back()->with('success', 'Time log berhasil diupdate.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate time log: ' . $e->getMessage());
        }
    }



    /**
     * ====================================
     * DELETE TIME LOG
     * ====================================
     * 
     * Method ini digunakan untuk menghapus time log.
     * User hanya bisa hapus time log miliknya sendiri.
     * 
     * @param TimeLog $timeLog - Instance time log yang akan dihapus
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TimeLog $timeLog)
    {
        try {
            $currentUser = Auth::user();



            // Authorization check
            if ($timeLog->user_id !== $currentUser->id) {
                abort(403, 'Anda tidak memiliki izin untuk menghapus time log ini.');
            }



            // Hapus time log
            $timeLog->delete();



            return redirect()->back()->with('success', 'Time log berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus time log: ' . $e->getMessage());
        }
    }



    /**
     * ====================================
     * GET TOTAL TIME BY CARD
     * ====================================
     * 
     * Method ini menghitung total waktu (SUM duration_minutes) untuk satu card.
     * Digunakan untuk menampilkan statistik total waktu yang sudah dihabiskan.
     * 
     * Return format:
     * {
     *   "total_minutes": 150,
     *   "total_hours": 2.5,
     *   "formatted": "2 jam 30 menit",
     *   "logs_count": 5
     * }
     * 
     * @param int $cardId - ID card yang akan dihitung
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalTimeByCard($cardId)
    {
        try {
            // Cari card
            $card = Card::findOrFail($cardId);



            // Authorization check
            $currentUser = Auth::user();
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember && $project->created_by !== $currentUser->id) {
                abort(403, 'Anda tidak memiliki izin untuk melihat data ini.');
            }



            // Hitung total durasi hanya untuk time log yang sudah selesai
            $totalMinutes = TimeLog::where('card_id', $cardId)
                ->whereNotNull('end_time')
                ->sum('duration_minutes');



            // Hitung jumlah time logs
            $logsCount = TimeLog::where('card_id', $cardId)->count();



            // Format durasi
            $hours = intval($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $formatted = sprintf('%d jam %d menit', $hours, $minutes);



            return response()->json([
                'success' => true,
                'total_minutes' => $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 2),
                'formatted' => $formatted,
                'logs_count' => $logsCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan total waktu: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * GET TOTAL TIME BY SUBTASK
     * ====================================
     * 
     * Method ini menghitung total waktu (SUM duration_minutes) untuk satu subtask.
     * 
     * Return format sama seperti getTotalTimeByCard.
     * 
     * @param int $subtaskId - ID subtask yang akan dihitung
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalTimeBySubtask($subtaskId)
    {
        try {
            // Cari subtask
            $subtask = Subtask::findOrFail($subtaskId);



            // Authorization check
            $currentUser = Auth::user();
            $card = $subtask->card;
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember && $project->created_by !== $currentUser->id) {
                abort(403, 'Anda tidak memiliki izin untuk melihat data ini.');
            }



            // Hitung total durasi hanya untuk time log yang sudah selesai
            $totalMinutes = TimeLog::where('subtask_id', $subtaskId)
                ->whereNotNull('end_time')
                ->sum('duration_minutes');



            // Hitung jumlah time logs
            $logsCount = TimeLog::where('subtask_id', $subtaskId)->count();



            // Format durasi
            $hours = intval($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $formatted = sprintf('%d jam %d menit', $hours, $minutes);



            return response()->json([
                'success' => true,
                'total_minutes' => $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 2),
                'formatted' => $formatted,
                'logs_count' => $logsCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan total waktu: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * GET ONGOING TIMER (Helper method untuk view)
     * ====================================
     * 
     * Method ini mengambil timer yang sedang berjalan milik user.
     * Digunakan untuk menampilkan status timer di UI.
     * 
     * @return TimeLog|null
     */
    public function getOngoingTimer()
    {
        return TimeLog::where('user_id', Auth::id())
            ->whereNull('end_time')
            ->with(['card', 'subtask'])
            ->first();
    }
}
