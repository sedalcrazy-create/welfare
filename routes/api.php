<?php

use Illuminate\Support\Facades\Route;

// API routes will be implemented later
// For now, just a simple status endpoint
Route::get('/status', function () {
    return response()->json(['status' => 'ok', 'message' => 'Welfare API is running']);
});
