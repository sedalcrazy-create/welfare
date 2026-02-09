<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Close registration for lotteries
Schedule::command('lottery:close-registration')
    ->dailyAt('23:59')
    ->description('Close lottery registration');

// Auto-draw lotteries on draw date
Schedule::command('lottery:auto-draw')
    ->dailyAt('10:00')
    ->description('Auto draw lotteries');

// Send reminders
Schedule::command('lottery:send-reminders')
    ->dailyAt('09:00')
    ->description('Send lottery reminders');

// Clean up expired entries
Schedule::command('lottery:cleanup-expired')
    ->daily()
    ->description('Clean up expired lottery entries');
