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
use App\Http\Controllers\PersonnelRequestController;
use App\Http\Controllers\IntroductionLetterController;
use App\Http\Controllers\Admin\UserQuotaController;
use App\Http\Controllers\Admin\UserCenterQuotaController;
use App\Http\Controllers\Admin\RegistrationControlController;

Route::get('/', function () {
    return redirect()->route('login');
});

// User Guide - Public access
Route::get('/user-guide', function () {
    return response()->file(public_path('user-guide.html'));
})->name('user-guide');

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

    // Personnel Requests Management (Phase 1)
    Route::resource('personnel-requests', PersonnelRequestController::class);
    Route::patch('personnel-requests/{personnelRequest}/approve', [PersonnelRequestController::class, 'approve'])
        ->name('personnel-requests.approve');
    Route::patch('personnel-requests/{personnelRequest}/reject', [PersonnelRequestController::class, 'reject'])
        ->name('personnel-requests.reject');

    // Introduction Letters Management (Phase 1)
    Route::resource('introduction-letters', IntroductionLetterController::class)->except(['edit', 'update']);
    Route::patch('introduction-letters/{introductionLetter}/cancel', [IntroductionLetterController::class, 'cancel'])
        ->name('introduction-letters.cancel');
    Route::patch('introduction-letters/{introductionLetter}/mark-as-used', [IntroductionLetterController::class, 'markAsUsed'])
        ->name('introduction-letters.mark-as-used');
    Route::get('introduction-letters/{introductionLetter}/print', [IntroductionLetterController::class, 'print'])
        ->name('introduction-letters.print');

    // Admin Management (Admin only)
    Route::prefix('admin')->name('admin.')->middleware('role:super_admin|admin')->group(function () {
        // Old per-user quota (deprecated but keep for backward compatibility)
        Route::get('user-quota', [UserQuotaController::class, 'index'])->name('user-quota.index');
        Route::patch('user-quota/{user}', [UserQuotaController::class, 'update'])->name('user-quota.update');
        Route::patch('user-quota/{user}/reset-used', [UserQuotaController::class, 'resetUsed'])->name('user-quota.reset-used');

        // New per-center quota management
        Route::get('user-center-quota', [UserCenterQuotaController::class, 'index'])->name('user-center-quota.index');
        Route::patch('user-center-quota/{user}/{center}', [UserCenterQuotaController::class, 'update'])->name('user-center-quota.update');
        Route::patch('user-center-quota/{user}/{center}/reset', [UserCenterQuotaController::class, 'reset'])->name('user-center-quota.reset');
        Route::post('user-center-quota/{user}/bulk-update', [UserCenterQuotaController::class, 'bulkUpdate'])->name('user-center-quota.bulk-update');

        // Registration control management
        Route::get('registration-control', [RegistrationControlController::class, 'index'])->name('registration-control.index');
        Route::post('registration-control', [RegistrationControlController::class, 'store'])->name('registration-control.store');
        Route::patch('registration-control/{registrationControl}', [RegistrationControlController::class, 'update'])->name('registration-control.update');
        Route::patch('registration-control/{registrationControl}/toggle', [RegistrationControlController::class, 'toggleStatus'])->name('registration-control.toggle');
        Route::delete('registration-control/{registrationControl}', [RegistrationControlController::class, 'destroy'])->name('registration-control.destroy');

        // Phase 1: Quota Management (NEW)
        Route::prefix('quotas')->name('quotas.')->group(function () {
            Route::get('users/{user}', [\App\Http\Controllers\Admin\QuotaController::class, 'index'])->name('index');
            Route::post('users/{user}/allocate', [\App\Http\Controllers\Admin\QuotaController::class, 'allocate'])->name('allocate');
            Route::patch('quotas/{quota}', [\App\Http\Controllers\Admin\QuotaController::class, 'update'])->name('update');
            Route::post('quotas/{quota}/reset', [\App\Http\Controllers\Admin\QuotaController::class, 'reset'])->name('reset');
            Route::post('quotas/{quota}/increase', [\App\Http\Controllers\Admin\QuotaController::class, 'increase'])->name('increase');
            Route::post('quotas/{quota}/decrease', [\App\Http\Controllers\Admin\QuotaController::class, 'decrease'])->name('decrease');
        });

        // Phase 1: Personnel Approval (NEW)
        Route::prefix('personnel-approvals')->name('personnel-approvals.')->group(function () {
            Route::get('pending', [\App\Http\Controllers\Admin\PersonnelApprovalController::class, 'pending'])->name('pending');
            Route::get('{personnel}', [\App\Http\Controllers\Admin\PersonnelApprovalController::class, 'show'])->name('show');
            Route::post('{personnel}/approve', [\App\Http\Controllers\Admin\PersonnelApprovalController::class, 'approve'])->name('approve');
            Route::post('{personnel}/reject', [\App\Http\Controllers\Admin\PersonnelApprovalController::class, 'reject'])->name('reject');
            Route::post('bulk-approve', [\App\Http\Controllers\Admin\PersonnelApprovalController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('bulk-reject', [\App\Http\Controllers\Admin\PersonnelApprovalController::class, 'bulkReject'])->name('bulk-reject');
        });
    });
});
