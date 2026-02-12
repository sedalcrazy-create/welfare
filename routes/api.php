<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PersonnelRequestController;
use App\Http\Controllers\Api\CenterController;
use App\Http\Controllers\Api\PeriodController;

// API routes will be implemented later
// For now, just a simple status endpoint
Route::get('/status', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Welfare API is running',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String()
    ]);
});

// Public API for Bale Bot (Phase 1)
Route::prefix('v1')->group(function () {
    // Centers
    Route::get('/centers', [CenterController::class, 'index']);
    Route::get('/centers/{center}', [CenterController::class, 'show']);

    // Periods
    Route::get('/periods', [PeriodController::class, 'index']);
    Route::get('/periods/{period}', [PeriodController::class, 'show']);
    Route::get('/centers/{center}/periods', [PeriodController::class, 'byCenter']);

    // Personnel Requests (Bale Bot)
    Route::post('/personnel-requests/register', [PersonnelRequestController::class, 'register']);
    Route::post('/personnel-requests/check-status', [PersonnelRequestController::class, 'checkStatus']);
    Route::get('/personnel-requests/letters', [PersonnelRequestController::class, 'getLetters']);
});

// Legacy Bale Bot API (Backward compatibility)
Route::prefix('bale')->group(function () {
    // Get available centers
    Route::get('/centers', [CenterController::class, 'index']);

    // Register new personnel request
    Route::post('/register', [PersonnelRequestController::class, 'register']);

    // Check request status
    Route::post('/check-status', [PersonnelRequestController::class, 'checkStatus']);

    // Get personnel introduction letters
    Route::post('/letters', [PersonnelRequestController::class, 'getLetters']);
});
