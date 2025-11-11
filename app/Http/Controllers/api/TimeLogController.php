<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\TimeLog;
use App\Models\Card;
use App\Models\Subtask;
use App\Models\CardAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * TimeLogController API
 * 
 * API Controller untuk mengelola time tracking di aplikasi mobile (Flutter).
 * 
 * Fitur utama:
 * - Start/Stop tracking waktu kerja
 * - Get time logs milik user
 * - Get time logs per card/subtask
 * - Calculate total duration
 * - Update dan delete time log
 * 
 * Semua response menggunakan format JSON standar REST API.
 * Authentication menggunakan Laravel Sanctum (Bearer Token).
 * 
 * @package App\Http\Controllers\api
 */
class TimeLogController extends Controller
{
    /**
     * ====================================
     * GET ALL TIME LOGS (User's Time Logs)
     * ====================================
     * 
     * Mendapatkan semua time logs milik user yang sedang login.
     * Diurutkan dari yang terbaru.
     * 
     * Query Parameters:
     * - status: 'ongoing' | 'completed' (optional)
     * - card_id: filter by card (optional)
     * - subtask_id: filter by subtask (optional)
     * - per_page: jumlah data per halaman (default: 20)
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Time logs berhasil diambil",
     *   "data": [...],
     *   "meta": {
     *     "total": 10,
     *     "ongoing_count": 1,
     *     "completed_count": 9,
     *     "current_page": 1,
     *     "per_page": 20
     *   }
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Ambil user yang sedang login dari token
            $userId = auth()->user()->id;



            // Query builder dengan eager loading
            $query = TimeLog::with(['card.board', 'subtask', 'user'])
                ->where('user_id', $userId);



            // Filter berdasarkan status (ongoing/completed)
            if ($request->filled('status')) {
                if ($request->status === 'ongoing') {
                    $query->whereNull('end_time');
                } elseif ($request->status === 'completed') {
                    $query->whereNotNull('end_time');
                }
            }



            // Filter berdasarkan card_id
            if ($request->filled('card_id')) {
                $query->where('card_id', $request->card_id);
            }



            // Filter berdasarkan subtask_id
            if ($request->filled('subtask_id')) {
                $query->where('subtask_id', $request->subtask_id);
            }



            // Pagination
            $perPage = $request->input('per_page', 20);
            $timeLogs = $query->orderBy('start_time', 'desc')->paginate($perPage);



            // Hitung statistik
            $ongoingCount = TimeLog::where('user_id', $userId)->whereNull('end_time')->count();
            $completedCount = TimeLog::where('user_id', $userId)->whereNotNull('end_time')->count();



            // Transform data untuk response
            $data = $timeLogs->map(function($log) {
                return [
                    'id' => $log->id,
                    'card_id' => $log->card_id,
                    'card_title' => $log->card ? $log->card->title : null,
                    'board_name' => $log->card && $log->card->board ? $log->card->board->board_name : null,
                    'subtask_id' => $log->subtask_id,
                    'subtask_name' => $log->subtask ? $log->subtask->subtask_name : null,
                    'user_id' => $log->user_id,
                    'user_name' => $log->user->username,
                    'start_time' => $log->start_time->toIso8601String(),
                    'end_time' => $log->end_time ? $log->end_time->toIso8601String() : null,
                    'duration_minutes' => $log->duration_minutes,
                    'duration_formatted' => $this->formatDuration($log->duration_minutes),
                    'description' => $log->description,
                    'is_ongoing' => $log->isOngoing(),
                    'created_at' => $log->created_at->toIso8601String(),
                    'updated_at' => $log->updated_at->toIso8601String(),
                ];
            });



            return response()->json([
                'success' => true,
                'message' => 'Time logs berhasil diambil',
                'data' => $data,
                'meta' => [
                    'total' => $timeLogs->total(),
                    'ongoing_count' => $ongoingCount,
                    'completed_count' => $completedCount,
                    'current_page' => $timeLogs->currentPage(),
                    'last_page' => $timeLogs->lastPage(),
                    'per_page' => $timeLogs->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil time logs: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * GET ONGOING TIMER
     * ====================================
     * 
     * Mendapatkan timer yang sedang berjalan milik user.
     * Digunakan untuk cek apakah ada timer active dan menampilkan di UI Flutter.
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Ongoing timer found",
     *   "data": {...} atau null
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOngoingTimer()
    {
        try {
            $userId = auth()->user()->id;



            $ongoingTimer = TimeLog::with(['card.board', 'subtask', 'user'])
                ->where('user_id', $userId)
                ->whereNull('end_time')
                ->first();



            if (!$ongoingTimer) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada timer yang sedang berjalan',
                    'data' => null
                ]);
            }



            // Calculate elapsed time in real-time
            $elapsedMinutes = $ongoingTimer->start_time->diffInMinutes(Carbon::now('Asia/Jakarta'));



            return response()->json([
                'success' => true,
                'message' => 'Ongoing timer ditemukan',
                'data' => [
                    'id' => $ongoingTimer->id,
                    'card_id' => $ongoingTimer->card_id,
                    'card_title' => $ongoingTimer->card ? $ongoingTimer->card->title : null,
                    'board_name' => $ongoingTimer->card && $ongoingTimer->card->board ? $ongoingTimer->card->board->board_name : null,
                    'subtask_id' => $ongoingTimer->subtask_id,
                    'subtask_name' => $ongoingTimer->subtask ? $ongoingTimer->subtask->subtask_name : null,
                    'start_time' => $ongoingTimer->start_time->toIso8601String(),
                    'elapsed_minutes' => $elapsedMinutes,
                    'elapsed_formatted' => $this->formatDuration($elapsedMinutes),
                    'description' => $ongoingTimer->description,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ongoing timer: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * START TIME TRACKING
     * ====================================
     * 
     * Memulai tracking waktu kerja untuk card atau subtask.
     * 
     * Request Body:
     * {
     *   "card_id": 1,           // Required jika tidak ada subtask_id
     *   "subtask_id": 2,        // Optional, jika ada maka card_id akan auto-filled
     *   "description": "Working on login feature"  // Optional
     * }
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Time tracking dimulai",
     *   "data": {
     *      "id": 1,
     *      "card_id": 1,
     *      "card_title": "Login Feature",
     *      "board_name": "Development Sprint",
     *      "subtask_id": 2,
     *      "subtask_name": "Create login page",
     *      "start_time": "2024-06-01T10:00:00Z",
     *      "description": "Working on login feature"
     *   }
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startTracking(Request $request)
    {
        try {
            // Validasi input
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
                return response()->json([
                    'success' => false,
                    'message' => 'Card ID atau Subtask ID harus diisi'
                ], 422);
            }



            $currentUser = auth()->user();



            // Cek apakah user sudah punya timer yang sedang berjalan
            $ongoingTimer = TimeLog::where('user_id', $currentUser->id)
                ->whereNull('end_time')
                ->first();



            if ($ongoingTimer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda masih memiliki timer yang sedang berjalan',
                    'data' => [
                        'ongoing_timer_id' => $ongoingTimer->id,
                        'card_title' => $ongoingTimer->card ? $ongoingTimer->card->title : null
                    ]
                ], 400);
            }



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
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk tracking waktu di card ini'
                ], 403);
            }



            // Buat time log baru dengan start_time = sekarang
            $timeLog = TimeLog::create([
                'card_id' => $validatedData['card_id'],
                'subtask_id' => $validatedData['subtask_id'],
                'user_id' => $currentUser->id,
                'start_time' => Carbon::now('Asia/Jakarta'),
                'end_time' => null,
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



            // Load relationships untuk response
            $timeLog->load(['card.board', 'subtask']);



            return response()->json([
                'success' => true,
                'message' => 'Time tracking dimulai',
                'data' => [
                    'id' => $timeLog->id,
                    'card_id' => $timeLog->card_id,
                    'card_title' => $timeLog->card ? $timeLog->card->title : null,
                    'board_name' => $timeLog->card && $timeLog->card->board ? $timeLog->card->board->board_name : null,
                    'subtask_id' => $timeLog->subtask_id,
                    'subtask_name' => $timeLog->subtask ? $timeLog->subtask->subtask_name : null,
                    'start_time' => $timeLog->start_time->toIso8601String(),
                    'description' => $timeLog->description,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai time tracking: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * STOP TIME TRACKING
     * ====================================
     * 
     * Menghentikan tracking waktu kerja.
     * 
     * Request Body:
     * {
     *   "description": "Completed login feature"  // Optional
     * }
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Time tracking dihentikan",
     *   "data": {
     *     "duration_minutes": 150,
     *     "duration_formatted": "2 jam 30 menit"
     *   }
     * }
     * 
     * @param Request $request
     * @param int $id - Time log ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopTracking(Request $request, $id)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'description' => 'nullable|string|max:1000'
            ]);



            $timeLog = TimeLog::findOrFail($id);
            $currentUser = auth()->user();



            // Authorization check
            if ($timeLog->user_id !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghentikan time log ini'
                ], 403);
            }



            // Validasi bahwa timer masih berjalan
            if (!$timeLog->isOngoing()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time log ini sudah dihentikan sebelumnya'
                ], 400);
            }



            // Set end_time dan hitung durasi
            $endTime = Carbon::now('Asia/Jakarta');
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



            // Load relationships
            $timeLog->load(['card.board', 'subtask']);



            return response()->json([
                'success' => true,
                'message' => 'Time tracking dihentikan',
                'data' => [
                    'id' => $timeLog->id,
                    'card_id' => $timeLog->card_id,
                    'card_title' => $timeLog->card ? $timeLog->card->title : null,
                    'board_name' => $timeLog->card && $timeLog->card->board ? $timeLog->card->board->board_name : null,
                    'subtask_id' => $timeLog->subtask_id,
                    'subtask_name' => $timeLog->subtask ? $timeLog->subtask->subtask_name : null,
                    'start_time' => $timeLog->start_time->toIso8601String(),
                    'end_time' => $timeLog->end_time->toIso8601String(),
                    'duration_minutes' => $timeLog->duration_minutes,
                    'duration_formatted' => $this->formatDuration($timeLog->duration_minutes),
                    'description' => $timeLog->description,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan time tracking: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * UPDATE TIME LOG
     * ====================================
     * 
     * Update description atau data time log yang sudah selesai.
     * 
     * Request Body:
     * {
     *   "description": "Updated description"
     * }
     * 
     * @param Request $request
     * @param int $id - Time log ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'description' => 'nullable|string|max:1000'
            ]);



            $timeLog = TimeLog::findOrFail($id);
            $currentUser = auth()->user();



            // Authorization check
            if ($timeLog->user_id !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengupdate time log ini'
                ], 403);
            }



            // Validasi bahwa time log sudah selesai
            if ($timeLog->isOngoing()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa update time log yang masih berjalan'
                ], 400);
            }



            // Update data
            $timeLog->update([
                'description' => $validatedData['description']
            ]);



            // Load relationships
            $timeLog->load(['card.board', 'subtask']);



            return response()->json([
                'success' => true,
                'message' => 'Time log berhasil diupdate',
                'data' => [
                    'id' => $timeLog->id,
                    'description' => $timeLog->description,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate time log: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * DELETE TIME LOG
     * ====================================
     * 
     * Menghapus time log.
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Time log berhasil dihapus"
     * }
     * 
     * @param int $id - Time log ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $timeLog = TimeLog::findOrFail($id);
            $currentUser = auth()->user();



            // Authorization check
            if ($timeLog->user_id !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus time log ini'
                ], 403);
            }



            // Hapus time log
            $timeLog->delete();



            return response()->json([
                'success' => true,
                'message' => 'Time log berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus time log: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * GET TOTAL TIME BY CARD
     * ====================================
     * 
     * Menghitung total waktu untuk satu card.
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Total waktu berhasil dihitung",
     *   "data": {
     *     "card_id": 1,
     *     "card_title": "Login Feature",
     *     "total_minutes": 150,
     *     "total_hours": 2.5,
     *     "formatted": "2 jam 30 menit",
     *     "logs_count": 5
     *   }
     * }
     * 
     * @param int $cardId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalTimeByCard($cardId)
    {
        try {
            // Cari card
            $card = Card::findOrFail($cardId);



            // Authorization check
            $currentUser = auth()->user();
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember && $project->created_by !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk melihat data ini'
                ], 403);
            }



            // Hitung total durasi hanya untuk time log yang sudah selesai
            $totalMinutes = TimeLog::where('card_id', $cardId)
                ->whereNotNull('end_time')
                ->sum('duration_minutes');



            // Hitung jumlah time logs
            $logsCount = TimeLog::where('card_id', $cardId)->count();



            return response()->json([
                'success' => true,
                'message' => 'Total waktu berhasil dihitung',
                'data' => [
                    'card_id' => $card->id,
                    'card_title' => $card->title,
                    'total_minutes' => $totalMinutes,
                    'total_hours' => round($totalMinutes / 60, 2),
                    'formatted' => $this->formatDuration($totalMinutes),
                    'logs_count' => $logsCount
                ]
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
     * Menghitung total waktu untuk satu subtask.
     * 
     * Response format sama seperti getTotalTimeByCard.
     * 
     * @param int $subtaskId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalTimeBySubtask($subtaskId)
    {
        try {
            // Cari subtask
            $subtask = Subtask::findOrFail($subtaskId);



            // Authorization check
            $currentUser = auth()->user();
            $card = $subtask->card;
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember && $project->created_by !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk melihat data ini'
                ], 403);
            }



            // Hitung total durasi hanya untuk time log yang sudah selesai
            $totalMinutes = TimeLog::where('subtask_id', $subtaskId)
                ->whereNotNull('end_time')
                ->sum('duration_minutes');



            // Hitung jumlah time logs
            $logsCount = TimeLog::where('subtask_id', $subtaskId)->count();



            return response()->json([
                'success' => true,
                'message' => 'Total waktu berhasil dihitung',
                'data' => [
                    'subtask_id' => $subtask->id,
                    'subtask_name' => $subtask->subtask_name,
                    'total_minutes' => $totalMinutes,
                    'total_hours' => round($totalMinutes / 60, 2),
                    'formatted' => $this->formatDuration($totalMinutes),
                    'logs_count' => $logsCount
                ]
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
     * HELPER METHOD: FORMAT DURATION
     * ====================================
     * 
     * Format durasi dari menit ke format "X jam Y menit".
     * 
     * @param int $minutes
     * @return string
     */
    private function formatDuration($minutes)
    {
        if ($minutes === 0 || $minutes === null) {
            return '0 menit';
        }

        $hours = intval($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return sprintf('%d jam %d menit', $hours, $mins);
        } elseif ($hours > 0) {
            return sprintf('%d jam', $hours);
        } else {
            return sprintf('%d menit', $mins);
        }
    }



    /**
     * ====================================
     * UPDATE SUBTASK STATUS TO DONE
     * ====================================
     * 
     * Mengubah status subtask menjadi "done".
     * Bisa dipanggil langsung tanpa validasi khusus.
     * 
     * Request Body:
     * {
     *   "subtask_id": 1,
     *   "status": "done"  // atau "to do", "in progress"
     * }
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Status subtask berhasil diupdate",
     *   "data": {
     *     "subtask_id": 1,
     *     "status": "done"
     *   }
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSubtaskStatus(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'subtask_id' => 'required|exists:subtasks,id',
                'status' => 'required|in:to do,in progress,done'
            ]);



            $subtask = Subtask::findOrFail($validatedData['subtask_id']);
            $currentUser = auth()->user();



            // Authorization check - user harus member dari project
            $card = $subtask->card;
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember && $project->created_by !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengupdate subtask ini'
                ], 403);
            }



            // Update status subtask (langsung tanpa validasi tambahan)
            $subtask->update(['status' => $validatedData['status']]);



            return response()->json([
                'success' => true,
                'message' => 'Status subtask berhasil diupdate ke ' . $validatedData['status'],
                'data' => [
                    'subtask_id' => $subtask->id,
                    'subtask_name' => $subtask->subtask_name,
                    'status' => $subtask->status,
                    'card_id' => $card->id,
                    'card_title' => $card->title
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status subtask: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================
     * UPDATE CARD STATUS TO REVIEW
     * ====================================
     * 
     * Mengubah status card dari "in progress" ke "review".
     * 
     * VALIDASI KHUSUS:
     * - Hanya bisa update ke "review" jika SEMUA subtask sudah berstatus "done"
     * - Jika masih ada subtask yang belum "done", akan return error
     * 
     * Request Body:
     * {
     *   "card_id": 1,
     *   "status": "review"  // atau "todo", "in progress", "done"
     * }
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Status card berhasil diupdate",
     *   "data": {
     *     "card_id": 1,
     *     "status": "review",
     *     "all_subtasks_done": true
     *   }
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCardStatus(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'card_id' => 'required|exists:cards,id',
                'status' => 'required|in:todo,in progress,review,done'
            ]);



            $card = Card::with('subtasks')->findOrFail($validatedData['card_id']);
            $currentUser = auth()->user();



            // Authorization check - user harus member dari project
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            
            if (!$projectMember && $project->created_by !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengupdate card ini'
                ], 403);
            }



            // VALIDASI KHUSUS: Jika mau update ke "review" atau "done"
            // Cek apakah semua subtask sudah berstatus "done"
            if (in_array($validatedData['status'], ['review', 'done'])) {
                $hasSubtasks = $card->subtasks->count() > 0;
                
                if ($hasSubtasks) {
                    // Cek apakah ada subtask yang belum "done"
                    $unfinishedSubtasks = $card->subtasks->filter(function($subtask) {
                        return $subtask->status !== 'done';
                    });

                    if ($unfinishedSubtasks->count() > 0) {
                        // Masih ada subtask yang belum selesai
                        $unfinishedList = $unfinishedSubtasks->map(function($st) {
                            return [
                                'id' => $st->id,
                                'name' => $st->subtask_name,
                                'status' => $st->status
                            ];
                        })->values();

                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak bisa mengubah status card ke "' . $validatedData['status'] . '" karena masih ada subtask yang belum selesai',
                            'data' => [
                                'unfinished_subtasks_count' => $unfinishedSubtasks->count(),
                                'total_subtasks' => $card->subtasks->count(),
                                'unfinished_subtasks' => $unfinishedList
                            ]
                        ], 400);
                    }
                }
            }



            // Update status card
            $card->update(['status' => $validatedData['status']]);



            return response()->json([
                'success' => true,
                'message' => 'Status card berhasil diupdate ke ' . $validatedData['status'],
                'data' => [
                    'card_id' => $card->id,
                    'card_title' => $card->title,
                    'status' => $card->status,
                    'all_subtasks_done' => $card->subtasks->every(fn($st) => $st->status === 'done'),
                    'total_subtasks' => $card->subtasks->count()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status card: ' . $e->getMessage()
            ], 500);
        }
    }
}
