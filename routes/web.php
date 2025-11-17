<?php

use App\Models\Card;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\web\CardController;
use App\Http\Controllers\web\BoardController;
use App\Http\Controllers\web\CommentController;
use App\Http\Controllers\web\ProjectController;
use App\Http\Controllers\web\SubtaskController;
use App\Http\Controllers\web\TimeLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\web\CardReviewController;
use App\Http\Controllers\web\AdminSettingsController;
use App\Http\Controllers\web\ProjectMemberController;
use App\Http\Controllers\web\AdminDashboardController;
use App\Http\Controllers\web\AuthenticationController;
use App\Http\Controllers\web\CardAssignmentController;
use App\Http\Controllers\web\AdminStatisticsController;
use App\Http\Controllers\web\AdminActivityLogController;
use App\Http\Controllers\web\TeamLeaderDashboardController;

/**
 * ROUTE DOCUMENTATION
 * ===================
 * 
 * File ini mendefinisikan semua route untuk aplikasi Project Management:
 * - Authentication routes (login, register, dll)
 * - Project CRUD routes 
 * - Dashboard routes
 * - Profile routes
 */

/*
|--------------------------------------------------------------------------
| Guest Routes (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/

// Halaman utama - redirect ke login jika belum login, ke dashboard jika sudah login
Route::get('/', fn() => Auth::check() ? redirect()->route('dashboard') : redirect()->route('login'))
->name('home');

// Authentication routes (login, register, forgot password, dll)
Route::get('/login', fn() => view('auth.login') )
    ->name('login');

Route::get('/register', fn() => view('auth.register') )
    ->name('register');




Route::post('/login', [AuthenticationController::class, 'login'])
->name('login.attempt');

Route::post('/register', [AuthenticationController::class, 'register'])
->name('register.store');













/*
|--------------------------------------------------------------------------
| Authenticated Routes (Perlu Login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    /**
     * Dashboard Routes
     * ===============
     * Route untuk halaman dashboard utama dengan smart routing:
     * - Admin → admin.dashboard
     * - Team Lead → team-leader.dashboard
     * - Member with projects → member.dashboard
     * - Member without projects → unassigned.dashboard
     */
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Check if admin
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        // Check if team leader
        $isTeamLeader = \App\Models\ProjectMember::where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
        
        if ($isTeamLeader) {
            return redirect()->route('team-leader.dashboard');
        }
        
        // Check if member (developer/designer)
        $isMember = \App\Models\ProjectMember::where('user_id', $user->id)
            ->whereIn('role', ['developer', 'designer'])
            ->exists();
        
        if ($isMember) {
            // Member has project assignments
            return redirect()->route('member.dashboard');
        }
        
        // User not assigned to any project
        return redirect()->route('unassigned.dashboard');
    })->name('dashboard');


    /**
     * Profile Routes
     * ==============
     * Route untuk mengelola profile user
     */
    Route::get('/profile/edit', [\App\Http\Controllers\web\ProfileController::class, 'edit'])
        ->name('profile.edit');
    
    Route::put('/profile/update', [\App\Http\Controllers\web\ProfileController::class, 'update'])
        ->name('profile.update');
    
    Route::delete('/profile/delete-picture', [\App\Http\Controllers\web\ProfileController::class, 'deleteProfilePicture'])
        ->name('profile.delete-picture');




    /**
     * Admin Dashboard
     * ===============
     * Route untuk admin dashboard dengan analytics dan reporting
     * - Middleware: auth + admin (checked in controller)
     */
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard')
        ->middleware('admin');




    /**
     * Admin Activity Logs
     * ===================
     * Route untuk menampilkan system activity logs
     */
    Route::get('/admin/activity-logs', [AdminActivityLogController::class, 'index'])
        ->name('admin.activity-logs')
        ->middleware('admin');
    



    /**
     * Admin Statistics
     * ================
     * Route untuk menampilkan comprehensive analytics
     */
    Route::get('/admin/statistics', [AdminStatisticsController::class, 'index'])
        ->name('admin.statistics')
        ->middleware('admin');
    




    /**
     * Admin Settings
     * ==============
     * Route untuk system settings dan maintenance
     */
    Route::get('/admin/settings', [AdminSettingsController::class, 'index'])
        ->name('admin.settings')
        ->middleware('admin');
    
    // Settings Actions
    Route::post('/admin/settings/clear-cache', [AdminSettingsController::class, 'clearCache'])
        ->name('admin.settings.clear-cache')
        ->middleware('admin');
    
    Route::post('/admin/settings/optimize', [AdminSettingsController::class, 'optimize'])
        ->name('admin.settings.optimize')
        ->middleware('admin');
    
    Route::post('/admin/settings/clear-logs', [AdminSettingsController::class, 'clearLogs'])
        ->name('admin.settings.clear-logs')
        ->middleware('admin');
    
    Route::post('/admin/settings/run-migrations', [AdminSettingsController::class, 'runMigrations'])
        ->name('admin.settings.run-migrations')
        ->middleware('admin');
    
    
    
    /**
     * Project Routes
     * ==============
     * Route untuk mengelola project (CRUD)
     */
    Route::resource('projects', ProjectController::class)->names([
        'index' => 'projects.index',        // GET /projects
        'create' => 'projects.create',      // GET /projects/create
        'store' => 'projects.store',        // POST /projects
        'show' => 'projects.show',          // GET /projects/{project}
        'edit' => 'projects.edit',          // GET /projects/{project}/edit
        'update' => 'projects.update',      // PUT/PATCH /projects/{project}
        'destroy' => 'projects.destroy',    // DELETE /projects/{project}
    ]);

    
    
    
    
    /**
     * Project Routes
     * ==============
     * Route untuk mengelola project (CRUD)
     */
    Route::resource('boards', BoardController::class)->names([
        'index' => 'boards.index',        // GET /boards
        'create' => 'boards.create',      // GET /boards/create
        'store' => 'boards.store',        // POST /boards
        'show' => 'boards.show',          // GET /boards/{project}
        'edit' => 'boards.edit',          // GET /boards/{project}/edit
        'update' => 'boards.update',      // PUT/PATCH /boards/{project}
        'destroy' => 'boards.destroy',    // DELETE /projects/{project}
    ]);
    
    // Additional board routes
    Route::get('boards/{board}/members', [BoardController::class, 'getMembers'])->name('boards.members');







    /**
     * Card Routes
     * ===========
     * Route untuk mengelola cards dalam board kanban
     */
    Route::resource('cards', CardController::class)->names([
        'index' => 'cards.index',         // GET /cards
        'create' => 'cards.create',       // GET /cards/create  
        'store' => 'cards.store',         // POST /cards
        'show' => 'cards.show',           // GET /cards/{card}
        'edit' => 'cards.edit',           // GET /cards/{card}/edit
        'update' => 'cards.update',       // PUT/PATCH /cards/{card}
        'destroy' => 'cards.destroy',     // DELETE /cards/{card}
    ]);
    


    // Additional card routes
    Route::patch('cards/{card}/status', [CardController::class, 'updateStatus'])->name('cards.update-status');
    





    /**
     * Card Review Routes
     * ==================
     * Route untuk approve/reject card oleh Team Lead
     * - POST /cards/{card}/reviews       -> Create review (approve/reject dengan notes opsional)
     * - GET /cards/{card}/reviews        -> Get review history untuk card
     * - GET /my-card-reviews             -> Halaman review history untuk developer/designer
     * 
     * Authorization: Hanya Team Lead atau Admin untuk approve/reject
     *                Developer/Designer untuk melihat review history mereka
     * Feature: Realtime broadcast untuk notifikasi
     */
    Route::post('cards/{card}/reviews', [CardReviewController::class, 'store'])->name('cards.reviews.store');
    Route::get('cards/{card}/reviews', [CardReviewController::class, 'index'])->name('cards.reviews.index');
    Route::get('/my-card-reviews', [CardReviewController::class, 'myReviews'])->name('card-reviews.my-reviews');
    





    /**
     * Subtask Routes
     * ==============
     * Route untuk mengelola subtasks dalam card
     * Hanya accessible oleh team lead dan project member
     */
    Route::post('subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');
    Route::put('subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtasks.update');
    Route::patch('subtasks/{subtask}/status', [SubtaskController::class, 'updateStatus'])->name('subtasks.update-status');
    Route::delete('subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');
    
    
    
    



    /**
     * Time Tracking Routes
     * ====================
     * Route untuk mengelola time tracking (start/stop timer, view logs, calculate total)
     * 
     * Fitur utama:
     * - Start tracking waktu kerja pada card/subtask
     * - Stop tracking dan auto-calculate durasi
     * - Update description time log
     * - Delete time log
     * - Get total waktu per card
     * - Get total waktu per subtask
     * 
     * Method:
     * - POST /time-logs/start          -> Start tracking (card_id atau subtask_id required)
     * - POST /time-logs/{timeLog}/stop -> Stop tracking dan hitung durasi
     * - PUT /time-logs/{timeLog}       -> Update description
     * - DELETE /time-logs/{timeLog}    -> Delete time log (hanya owner)
     * - GET /time-logs/card/{cardId}   -> Get total waktu untuk card (JSON response)
     * - GET /time-logs/subtask/{subtaskId} -> Get total waktu untuk subtask (JSON response)
     */
    Route::post('time-logs/start', [TimeLogController::class, 'startTracking'])->name('time-logs.start');
    Route::post('time-logs/{timeLog}/stop', [TimeLogController::class, 'stopTracking'])->name('time-logs.stop');
    Route::put('time-logs/{timeLog}', [TimeLogController::class, 'update'])->name('time-logs.update');
    Route::delete('time-logs/{timeLog}', [TimeLogController::class, 'destroy'])->name('time-logs.destroy');
    
    // API-style routes untuk mendapatkan total waktu (return JSON)
    Route::get('time-logs/card/{cardId}', [TimeLogController::class, 'getTotalTimeByCard'])->name('time-logs.total-card');
    Route::get('time-logs/subtask/{subtaskId}', [TimeLogController::class, 'getTotalTimeBySubtask'])->name('time-logs.total-subtask');
    


    // ========================================
    // COMMENT ROUTES
    // ========================================
    // Routes untuk mengelola komentar pada card dan subtask
    // - Card comments: Semua role bisa lihat, Team Lead hanya bisa comment di card yang dibuat/ditugaskan
    // - Subtask comments: Hanya Developer/Designer yang bisa lihat dan manipulasi
    
    // Create comment (Card atau Subtask)
    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    
    // Update comment (hanya owner yang bisa edit)
    Route::put('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    
    // Delete comment (hanya owner yang bisa delete)
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    
    // API-style routes untuk mendapatkan komentar (return JSON untuk AJAX)
    Route::get('comments/card/{cardId}', [CommentController::class, 'getCommentsForCard'])->name('comments.card');
    Route::get('comments/subtask/{subtaskId}', [CommentController::class, 'getCommentsForSubtask'])->name('comments.subtask');
    
    
    




    /**
     * CARD ASSIGNMENTS ROUTES
     * ========================================
     * Routes untuk assign/unassign members ke card
     * - Hanya Team Lead atau Card Creator yang bisa assign
     * - Return JSON untuk AJAX request
     */
    Route::post('card-assignments/assign', [CardAssignmentController::class, 'assign'])->name('card-assignments.assign');
    Route::post('card-assignments/unassign', [CardAssignmentController::class, 'unassign'])->name('card-assignments.unassign');
    
    





    
    // Test route for simple cards view
    Route::get('cards-simple', function() {
        $cards = \App\Models\Card::with(['board', 'creator'])->paginate(12);
        return view('cards.simple', compact('cards'));
    })->name('cards.simple');
    
    /**
     * Additional Project Members Routes
     * ================================
     * Route tambahan untuk fitur khusus project members
     * PENTING: Letakkan sebelum resource routes agar tidak tertangkap sebagai {id}
     */
    
    // Route untuk search available users (AJAX endpoint untuk invite modal)
    Route::get('/project-members/search-users', [ProjectMemberController::class, 'searchUsers'])
        ->name('project-members.search-users');



    /**
     * Project Members Routes
     * =====================
     * Route untuk mengelola project members (CRUD)
     */
    Route::resource('project-members', ProjectMemberController::class)->names([
        'index' => 'project-members.index',        // GET /project-members
        'store' => 'project-members.store',        // POST /project-members
        'update' => 'project-members.update',      // PUT/PATCH /project-members/{project}
        'destroy' => 'project-members.destroy',    // DELETE /project-members/{project}
    ]);





    /**
     * Additional Project Routes
     * ========================
     * Route tambahan untuk fitur khusus project
     */
    
    // Route untuk menampilkan project yang dibuat oleh user yang sedang login
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])
        ->name('projects.my-projects');
    
    // Route untuk menampilkan project dimana user adalah anggota tim (list)
    Route::get('/joined-projects', [ProjectController::class, 'joinedProjects'])
        ->name('projects.joined-projects');
    
    // Route untuk redirect member ke project aktif mereka (single project)
    Route::get('/my-active-project', [ProjectController::class, 'myActiveProject'])
        ->name('projects.my-active-project');
    
    
    
    /**
     * Notification Routes
     * ==================
     * Route untuk notifikasi realtime (card reviewed, assigned, dll)
     */
    
    // Web route - halaman notifikasi
    Route::get('/notifications', [NotificationController::class, 'page'])
        ->name('notifications.page');
    
    // API routes untuk AJAX calls
    Route::prefix('api/notifications')->name('notifications.')->group(function () {
        // Get recent notifications untuk dropdown (limit 10)
        Route::get('/recent', [NotificationController::class, 'recent'])
            ->name('recent');
        
        // Get unread count untuk badge
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])
            ->name('unread-count');
        
        // Get all notifications dengan pagination dan filter
        Route::get('/', [NotificationController::class, 'index'])
            ->name('api.index');
        
        // Mark single notification as read
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->name('mark-read');
        
        // Mark all notifications as read
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('mark-all-read');
        
        // Delete single notification
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])
            ->name('destroy');
        
        // Delete all read notifications
        Route::delete('/read/all', [NotificationController::class, 'deleteAllRead'])
            ->name('delete-all-read');
    });
    
    


    
// Team Leader Dashboard Routes
Route::middleware('team.leader')->prefix('team-leader')->name('team-leader.')->group(function () {
    // Main dashboard
    Route::get('/dashboard', [TeamLeaderDashboardController::class, 'index'])
        ->name('dashboard');
    
    // Chart APIs
    Route::get('/dashboard/chart/task-status', [TeamLeaderDashboardController::class, 'getTaskStatusChart'])
        ->name('dashboard.chart.task-status');
    
    Route::get('/dashboard/chart/team-workload', [TeamLeaderDashboardController::class, 'getTeamWorkloadChart'])
        ->name('dashboard.chart.team-workload');
    
    // Cache management
    Route::post('/dashboard/clear-cache', [TeamLeaderDashboardController::class, 'clearCache'])
        ->name('dashboard.clear-cache');
});

// Member Dashboard Routes (Developer/Designer)
Route::middleware('member')->prefix('member')->name('member.')->group(function () {
    // Main dashboard
    Route::get('/dashboard', [\App\Http\Controllers\web\MemberDashboardController::class, 'index'])
        ->name('dashboard');
    
    // Task actions
    Route::post('/tasks/{card}/start', [\App\Http\Controllers\web\MemberDashboardController::class, 'startTask'])
        ->name('tasks.start');
    
    Route::post('/tasks/{card}/pause', [\App\Http\Controllers\web\MemberDashboardController::class, 'pauseTask'])
        ->name('tasks.pause');
    
    // Cache management
    Route::post('/dashboard/clear-cache', [\App\Http\Controllers\web\MemberDashboardController::class, 'clearCache'])
        ->name('dashboard.clear-cache');
});

// Unassigned Member Dashboard (Users without project assignments)
Route::middleware('auth')->prefix('unassigned')->name('unassigned.')->group(function () {
    // Main unassigned dashboard
    Route::get('/dashboard', [\App\Http\Controllers\web\UnassignedMemberDashboardController::class, 'index'])
        ->name('dashboard');
});

// API endpoint for assignment check (no prefix for easy access)
Route::middleware('auth')->get('/api/check-assignment', [\App\Http\Controllers\web\UnassignedMemberDashboardController::class, 'checkAssignment'])
    ->name('api.check-assignment');




    
    /**
     * Report Routes (ADMIN ONLY)
     * =========================
     * Route untuk laporan sistem
     */
    
    // Halaman laporan (admin only)
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index')
        ->middleware('admin');
    
    // API endpoint untuk data laporan (admin only)
    Route::get('/api/reports/data', [ReportController::class, 'getData'])
        ->name('reports.data')
        ->middleware('admin');

});


/**
 * Logout Route (POST method)
 * =========================
 */
Route::post('/logout', [AuthenticationController::class, 'logout'])
    ->name('logout');






        
    /**
     * Profile Routes
     * =============
     * Route untuk mengelola profile user
     */
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });










/*
|--------------------------------------------------------------------------
| Authentication Routes (Laravel Breeze/Sanctum)
|--------------------------------------------------------------------------
| 
| Route ini akan ditambahkan oleh Laravel Breeze untuk:
| - Login (POST /login)
| - Register (POST /register)
| - Logout (POST /logout)
| - Password reset
| - Email verification
*/

// require __DIR__.'/auth.php';


