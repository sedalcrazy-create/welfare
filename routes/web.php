<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CenterController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\LotteryController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ProvinceController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes - Requires authentication
Route::middleware(['auth'])->group(function () {
    // Dashboard - All authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Management Routes - Requires specific roles
// Authorization is handled by Policies in Controllers
Route::middleware(['auth', 'role:super_admin|admin|provincial_admin|operator'])->group(function () {
    // Centers Management
    Route::resource('centers', CenterController::class);
    Route::patch('centers/{center}/toggle-status', [CenterController::class, 'toggleStatus'])
        ->name('centers.toggle-status');

    // Units Management
    Route::resource('units', UnitController::class);
    Route::patch('units/{unit}/toggle-status', [UnitController::class, 'toggleStatus'])
        ->name('units.toggle-status');
    Route::post('units/import', [UnitController::class, 'import'])->name('units.import');

    // Periods Management
    Route::resource('periods', PeriodController::class);
    Route::patch('periods/{period}/change-status', [PeriodController::class, 'changeStatus'])
        ->name('periods.change-status');

    // AJAX endpoints
    Route::get('centers/{center}/seasons', [PeriodController::class, 'getSeasons'])
        ->name('centers.seasons');

    // Lotteries Management
    Route::resource('lotteries', LotteryController::class);
    Route::patch('lotteries/{lottery}/change-status', [LotteryController::class, 'changeStatus'])
        ->name('lotteries.change-status');
    Route::post('lotteries/{lottery}/draw', [LotteryController::class, 'draw'])
        ->name('lotteries.draw');
    Route::post('lotteries/{lottery}/entries/{entry}/approve', [LotteryController::class, 'approveEntry'])
        ->name('lotteries.entries.approve');
    Route::post('lotteries/{lottery}/entries/{entry}/reject', [LotteryController::class, 'rejectEntry'])
        ->name('lotteries.entries.reject');
    Route::get('centers/{center}/periods-available', [LotteryController::class, 'getPeriods'])
        ->name('centers.periods-available');

    // Reservations Management
    Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('reservations/{reservation}/check-in', [ReservationController::class, 'checkIn'])
        ->name('reservations.check-in');
    Route::post('reservations/{reservation}/check-out', [ReservationController::class, 'checkOut'])
        ->name('reservations.check-out');
    Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');
    Route::get('reservations/{reservation}/voucher', [ReservationController::class, 'printVoucher'])
        ->name('reservations.voucher');

    // Personnel Management
    Route::resource('personnel', PersonnelController::class);
    Route::post('personnel/import', [PersonnelController::class, 'import'])->name('personnel.import');

    // Provinces Management
    Route::resource('provinces', ProvinceController::class)->except(['create', 'store', 'destroy']);
    Route::post('provinces/recalculate-quotas', [ProvinceController::class, 'recalculateQuotas'])
        ->name('provinces.recalculate-quotas');
});
