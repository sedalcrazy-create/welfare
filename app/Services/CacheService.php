<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * پاک کردن cache مربوط به مرکز
     */
    public function forgetCenterCache(int $centerId): void
    {
        Cache::forget("center_{$centerId}_unit_stats");
    }

    /**
     * پاک کردن cache مربوط به واحد
     */
    public function forgetUnitCache(int $unitId): void
    {
        Cache::forget("unit_{$unitId}_reservation_stats");
    }

    /**
     * پاک کردن cache مربوط به دوره
     */
    public function forgetPeriodCache(int $periodId): void
    {
        Cache::forget("period_{$periodId}_stats");
    }

    /**
     * پاک کردن cache مربوط به قرعه‌کشی
     */
    public function forgetLotteryCache(int $lotteryId): void
    {
        Cache::forget("lottery_{$lotteryId}_stats");
    }

    /**
     * پاک کردن همه cache های لیست
     */
    public function forgetListCaches(): void
    {
        Cache::forget('active_centers');
        Cache::forget('active_provinces');
    }
}
