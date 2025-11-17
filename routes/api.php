<?php

use App\Http\Controllers\api\CardController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\BoardController;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\ProjectMemberController;
use App\Http\Controllers\api\TimeLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',  [AuthenticationController::class, 'login']);
Route::post('/register',  [AuthenticationController::class, 'register']);


Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // fitur project
    Route::apiResource('project', ProjectController::class)->middleware('admin');
    Route::get('/project', [ProjectController::class, 'index']);
    Route::get('/project/{project}', [ProjectController::class, 'show']);
    

    // fitur card dan card-related operations
    Route::apiResource('card', CardController::class);
    
    // fitur subtasks untuk todolist functionality
    Route::apiResource('subtasks', \App\Http\Controllers\api\SubtaskController::class)->names([
        'index' => 'api.subtasks.index',
        'store' => 'api.subtasks.store',
        'show' => 'api.subtasks.show',
        'update' => 'api.subtasks.update',
        'destroy' => 'api.subtasks.destroy',
    ]);
    Route::patch('subtasks/{subtask}/toggle', [\App\Http\Controllers\api\SubtaskController::class, 'toggle'])->name('api.subtasks.toggle');
    
    // fitur komentar dengan card relationship
    Route::apiResource('comments', \App\Http\Controllers\api\CommentController::class)
    ->names([
        'index' => 'api.comments.index',
        'store' => 'api.comments.store',
        'show' => 'api.comments.show',
        'update' => 'api.comments.update',
        'destroy' => 'api.comments.destroy',
    ]);
    Route::get('cards/{card}/comments', [\App\Http\Controllers\api\CommentController::class, 'byCard']);



    // ========================================
    // TIME TRACKING ROUTES (Time Log API)
    // ========================================
    // Routes untuk fitur time tracking di aplikasi Flutter
    
    // Get all time logs milik user (dengan filter & pagination)
    Route::get('time-logs', [TimeLogController::class, 'index']);
    
    // Get ongoing timer (cek apakah ada timer yang sedang berjalan)
    Route::get('time-logs/ongoing', [TimeLogController::class, 'getOngoingTimer']);
    
    // Start time tracking
    Route::post('time-logs/start', [TimeLogController::class, 'startTracking']);
    
    // Stop time tracking
    Route::post('time-logs/{id}/stop', [TimeLogController::class, 'stopTracking']);
    
    // Update time log (description)
    Route::put('time-logs/{id}', [TimeLogController::class, 'update']);
    
    // Delete time log
    Route::delete('time-logs/{id}', [TimeLogController::class, 'destroy']);
    
    // Get total time by card
    Route::get('time-logs/card/{cardId}/total', [TimeLogController::class, 'getTotalTimeByCard']);
    
    // Get total time by subtask
    Route::get('time-logs/subtask/{subtaskId}/total', [TimeLogController::class, 'getTotalTimeBySubtask']);
    
    // Update subtask status (langsung, tanpa validasi khusus)
    Route::patch('time-logs/subtask/status', [TimeLogController::class, 'updateSubtaskStatus']);
    
    // Update card status (dengan validasi: hanya bisa ke review jika semua subtask done)
    Route::patch('time-logs/card/status', [TimeLogController::class, 'updateCardStatus']);



    // fitur board
    Route::apiResource('board', BoardController::class);
    
    // project member
    // Route::apiResource('memberProject', ProjectMemberController::class)->middleware('admin');
    
    // fitur profil
    Route::apiResource('user', UserController::class)->except('store');
    Route::get('/me', function(Request $request) {
        $user = $request->user()->load([
            'createdProjects',              // Projects yang dibuat user
            'projectMemberships.project',   // Projects dimana user adalah member
            'createdCards.board',           // Cards yang dibuat user dengan board-nya
            'comments.card',                // Comments user dengan card-nya
            'timeLogs.card',                // Time logs user dengan card-nya
        ]);

        // Tambahan: Hitung statistik user
        $stats = [
            'total_comments' => $user->comments->count(),
            'total_time_logs' => $user->timeLogs->count(),
            'total_hours_tracked' => round($user->timeLogs->whereNotNull('end_time')->sum('duration_minutes') / 60, 2),
        ];

        return response()->json([
            'success' => true,
            'data' => $user,
            'stats' => $stats
        ], 200);
    });
    
    // Update profile (edit profile user yang sedang login)
    Route::put('/profile', [AuthenticationController::class, 'updateProfile']);

    // logout
    Route::post('/logout', [AuthenticationController::class, "logout"]);
    
 });
