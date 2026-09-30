<?php

use App\Http\Controllers\Api\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\Admin\EventController as AdminEventController;
use App\Http\Controllers\Api\Admin\DataController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ScanLogController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\ExportController;
use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\Panitia\RecapController;
use App\Http\Controllers\Api\Panitia\ScanController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PackageController;
use Illuminate\Support\Facades\Route;


// Public
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/registrations', [RegistrationController::class, 'store']);
Route::post('/registrations/check-status', 
    [RegistrationController::class, 'checkStatus']);
Route::post('/registrations/{registration_number}/payment-proof', 
    [RegistrationController::class, 'uploadPaymentProof']);
Route::get('/event/active', [EventController::class, 'active']);
Route::get('/contents', [ContentController::class, 'index']);
Route::get('/results', [ResultController::class, 'index']);
Route::get('/results/{registration_number}', [ResultController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/packages', [PackageController::class, 'index']);

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
        Route::apiResource('/events', AdminEventController::class);
        Route::put('/events/{id}/toggle-active', [AdminEventController::class, 'toggleActive']);
        Route::post('/events/{id}/reset', [AdminEventController::class, 'reset']);  
        Route::apiResource('/contents', AdminContentController::class);
        Route::apiResource('/users', UserController::class);
        Route::get('/participants', [DataController::class, 'participants']);
        Route::get('/registrations', [DataController::class, 'registrations']);
        Route::get('/registrations/{id}', [DataController::class, 'showRegistration']);
        Route::put('/registrations/{id}/verify', [DataController::class, 'verifyRegistration']);
        Route::get('/dashboard', [DataController::class, 'dashboard']);
        Route::get('/scan-logs', [ScanLogController::class, 'index']);
        Route::get('/scan-logs/{id}', [ScanLogController::class, 'show']);
        Route::get('/settings', [SettingController::class, 'index']);
        Route::post('/settings', [SettingController::class, 'store']);
        Route::get('/settings/{key}', [SettingController::class, 'show']);
        Route::put('/settings/{key}', [SettingController::class, 'update']);
        Route::delete('/settings/{key}', [SettingController::class, 'destroy']);
        Route::get('/export/participants', [ExportController::class, 'participants']);
        Route::get('/export/registrations', [ExportController::class, 'registrations']);
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);
    });

});