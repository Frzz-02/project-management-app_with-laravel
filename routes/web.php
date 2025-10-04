<?php

use App\Http\Controllers\web\AuthenticationController;
use App\Http\Controllers\web\ProjectController;
use Illuminate\Support\Facades\Route;

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
Route::get('/', fn() => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'))
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

// Route::middleware('auth')->group(function () {
    
    /**
     * Dashboard Routes
     * ===============
     * Route untuk halaman dashboard utama
     */
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


    
    
    
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
     * Additional Project Routes
     * ========================
     * Route tambahan untuk fitur khusus project
     */
    
    // Route untuk menampilkan project yang dibuat oleh user yang sedang login
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])
        ->name('projects.my-projects');
    
    // Route untuk menampilkan project dimana user adalah anggota tim
    Route::get('/joined-projects', [ProjectController::class, 'joinedProjects'])
        ->name('projects.joined-projects');






        
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


