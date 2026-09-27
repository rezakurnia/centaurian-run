<?php

use App\Http\Controllers\Api\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\Admin\DataController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\Panitia\RecapController;
use App\Http\Controllers\Api\Panitia\ScanController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ResultController;
use Illuminate\Support\Facades\Route;


// Public
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/registrations', [RegistrationController::class, 'store']);
Route::get('/event/active', [EventController::class, 'active']);
Route::get('/contents', [ContentController::class, 'index']);
Route::get('/results', [ResultController::class, 'index']);
Route::get('/results/{registration_number}', [ResultController::class, 'show']);

// Protected (butuh token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

// Panitia
    Route::prefix('panitia')->group(function () {
        Route::get('/recap/category', [RecapController::class, 'byCategory']);
        Route::get('/recap/participants', [RecapController::class, 'byRegistrationNumber']);
        Route::post('/start', [ScanController::class, 'start']);
        Route::post('/scan', [ScanController::class, 'scan']);
    });

// Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('/contents', AdminContentController::class);
        Route::apiResource('/users', UserController::class);
        Route::get('/participants', [DataController::class, 'participants']);
        Route::get('/registrations', [DataController::class, 'registrations']);
        Route::get('/registrations/{id}', [DataController::class, 'showRegistration']);
        Route::put('/registrations/{id}/verify', [DataController::class, 'verifyRegistration']);
        Route::get('/dashboard', [DataController::class, 'dashboard']);
    });

});