<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PersonnelRequestController;
use App\Http\Controllers\Api\CenterController;
use App\Http\Controllers\Api\PeriodController;
use App\Http\Controllers\Api\BaleWebhookController;
use App\Http\Controllers\Api\MiniApp\AuthController as MiniAppAuthController;
use App\Http\Controllers\Api\MiniApp\PersonnelController as MiniAppPersonnelController;
use App\Http\Controllers\Api\MiniApp\GuestsController as MiniAppGuestsController;
use App\Http\Controllers\Api\MiniApp\LettersController as MiniAppLettersController;

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

// Bale Bot Webhook (no CSRF protection needed - handled by token in URL)
Route::post('/bale/webhook/{token}', [BaleWebhookController::class, 'handle'])->name('bale.webhook');

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

// Bale Mini App API
Route::prefix('mini-app')->group(function () {
    // Public - Authentication
    Route::post('/auth/verify', [MiniAppAuthController::class, 'verify']);

    // Protected routes (require Sanctum authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::get('/auth/me', [MiniAppAuthController::class, 'me']);
        Route::post('/auth/logout', [MiniAppAuthController::class, 'logout']);

        // Personnel
        Route::get('/personnel/me', [MiniAppPersonnelController::class, 'me']);
        Route::post('/personnel/register', [MiniAppPersonnelController::class, 'register']);
        Route::patch('/personnel/update', [MiniAppPersonnelController::class, 'update']);
        Route::get('/personnel/provinces', [MiniAppPersonnelController::class, 'provinces']);

        // Guests
        Route::get('/guests', [MiniAppGuestsController::class, 'index']);
        Route::post('/guests', [MiniAppGuestsController::class, 'store']);
        Route::get('/guests/{guest}', [MiniAppGuestsController::class, 'show']);
        Route::patch('/guests/{guest}', [MiniAppGuestsController::class, 'update']);
        Route::delete('/guests/{guest}', [MiniAppGuestsController::class, 'destroy']);

        // Personnel Guests
        Route::get('/personnel-guests', [MiniAppGuestsController::class, 'personnelGuests']);
        Route::get('/personnel-guests/search', [MiniAppGuestsController::class, 'searchPersonnel']);
        Route::post('/personnel-guests', [MiniAppGuestsController::class, 'addPersonnelGuest']);
        Route::delete('/personnel-guests/{personnelGuest}', [MiniAppGuestsController::class, 'removePersonnelGuest']);

        // Introduction Letters
        Route::get('/letters', [MiniAppLettersController::class, 'index']);
        Route::post('/letters', [MiniAppLettersController::class, 'store']);
        Route::get('/letters/{letter}', [MiniAppLettersController::class, 'show']);
        Route::delete('/letters/{letter}/cancel', [MiniAppLettersController::class, 'cancel']);

        // Centers & Quotas
        Route::get('/centers', [MiniAppLettersController::class, 'centers']);
        Route::get('/quotas', [MiniAppLettersController::class, 'quotas']);
    });
});
