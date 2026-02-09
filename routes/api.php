<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PersonnelRequestController;
use App\Http\Controllers\Api\AdminController;

// API routes will be implemented later
// For now, just a simple status endpoint
Route::get('/status', function () {
    return response()->json(['status' => 'ok', 'message' => 'Welfare API is running']);
});

// Bale Bot API (Public routes)
Route::prefix('bale')->group(function () {
    // Get available centers
    Route::get('/centers', [PersonnelRequestController::class, 'getCenters']);

    // Register new personnel request
    Route::post('/register', [PersonnelRequestController::class, 'register']);

    // Check request status
    Route::post('/check-status', [PersonnelRequestController::class, 'checkStatus']);

    // Get personnel introduction letters
    Route::post('/letters', [PersonnelRequestController::class, 'getLetters']);
});
