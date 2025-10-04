<?php

use App\Http\Controllers\api\CardController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\BoardController;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\ProjectMemberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',  [AuthenticationController::class, 'login']);
Route::post('/register',  [AuthenticationController::class, 'register']);


Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // fitur project
    Route::apiResource('project', ProjectController::class)->middleware('admin');
    Route::get('/project', [ProjectController::class, 'index']);
    Route::get('/project/{project}', [ProjectController::class, 'show']);
    

    // fitur komentar
    Route::apiResource('comment', \App\Http\Controllers\api\CommentController::class);

    
    // project member
    // Route::apiResource('memberProject', ProjectMemberController::class)->middleware('admin');

    
    
    
    // fitur profil
    Route::apiResource('user', UserController::class)->except('store');
    Route::get('/me', function(Request $request) {
        dd($request->user());
    });
    
    
    
    
    // fitur card
    Route::apiResource('card', CardController::class);

    // fitur board
    Route::apiResource('board', BoardController::class);

    // logout
    Route::post('/logout', [AuthenticationController::class, "logout"]);
    
 });
