<?php

namespace App\Services;

use App\Models\User;
use App\Models\Center;
use App\Models\UserCenterQuota;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserQuotaService
{
    /**
     * بررسی سهمیه کاربر برای یک مرکز
     */
    public function checkQuota(User $user, Center $center): bool
    {
        $quota = UserCenterQuota::where('user_id', $user->id)
            ->where('center_id', $center->id)
            ->first();

        return $quota && $quota->quota_remaining > 0;
    }

    /**
     * دریافت سهمیه کاربر برای یک مرکز
     */
    public function getQuota(User $user, Center $center): ?UserCenterQuota
    {
        return UserCenterQuota::where('user_id', $user->id)
            ->where('center_id', $center->id)
            ->first();
    }

    /**
     * تخصیص سهمیه به کاربر برای یک مرکز
     */
    public function allocateQuota(User $user, Center $center, int $amount): UserCenterQuota
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('مقدار سهمیه نمی‌تواند منفی باشد');
        }

        return UserCenterQuota::updateOrCreate(
            [
                'user_id' => $user->id,
                'center_id' => $center->id,
            ],
            [
                'quota_total' => $amount,
            ]
        );
    }

    /**
     * افزایش سهمیه کاربر
     */
    public function increaseQuota(User $user, Center $center, int $amount): UserCenterQuota
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('مقدار افزایش باید مثبت باشد');
        }

        $quota = $this->getOrCreateQuota($user, $center);
        $quota->increment('quota_total', $amount);

        return $quota->fresh();
    }

    /**
     * کاهش سهمیه کاربر
     */
    public function decreaseQuota(User $user, Center $center, int $amount): UserCenterQuota
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('مقدار کاهش باید مثبت باشد');
        }

        $quota = $this->getOrCreateQuota($user, $center);

        if ($quota->quota_total < $amount) {
            throw new \InvalidArgumentException('سهمیه کل کمتر از مقدار کاهش است');
        }

        $quota->decrement('quota_total', $amount);

        return $quota->fresh();
    }

    /**
     * مصرف یک سهمیه (هنگام صدور معرفی‌نامه)
     */
    public function consumeQuota(User $user, Center $center): void
    {
        $quota = $this->getQuota($user, $center);

        if (!$quota) {
            throw new \RuntimeException('سهمیه‌ای برای این کاربر و مرکز تعریف نشده است');
        }

        if ($quota->quota_remaining <= 0) {
            throw new \RuntimeException('سهمیه کافی موجود نیست');
        }

        DB::transaction(function () use ($quota) {
            $quota->increment('quota_used');
        });
    }

    /**
     * برگرداندن سهمیه (هنگام لغو معرفی‌نامه)
     */
    public function refundQuota(User $user, Center $center): void
    {
        $quota = $this->getQuota($user, $center);

        if (!$quota) {
            throw new \RuntimeException('سهمیه‌ای برای این کاربر و مرکز یافت نشد');
        }

        if ($quota->quota_used <= 0) {
            throw new \RuntimeException('سهمیه استفاده‌شده‌ای برای برگشت وجود ندارد');
        }

        DB::transaction(function () use ($quota) {
            $quota->decrement('quota_used');
        });
    }

    /**
     * ریست سهمیه استفاده شده
     */
    public function resetUsedQuota(User $user, Center $center): UserCenterQuota
    {
        $quota = $this->getQuota($user, $center);

        if (!$quota) {
            throw new \RuntimeException('سهمیه‌ای برای این کاربر و مرکز یافت نشد');
        }

        $quota->update(['quota_used' => 0]);

        return $quota->fresh();
    }

    /**
     * دریافت خلاصه سهمیه‌های یک کاربر
     */
    public function getQuotaSummary(User $user): Collection
    {
        return UserCenterQuota::with('center')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($quota) {
                return [
                    'center_id' => $quota->center_id,
                    'center_name' => $quota->center->name,
                    'quota_total' => $quota->quota_total,
                    'quota_used' => $quota->quota_used,
                    'quota_remaining' => $quota->quota_remaining,
                    'usage_percentage' => $quota->quota_total > 0
                        ? round(($quota->quota_used / $quota->quota_total) * 100, 2)
                        : 0,
                ];
            });
    }

    /**
     * دریافت یا ایجاد سهمیه
     */
    private function getOrCreateQuota(User $user, Center $center): UserCenterQuota
    {
        return UserCenterQuota::firstOrCreate(
            [
                'user_id' => $user->id,
                'center_id' => $center->id,
            ],
            [
                'quota_total' => 0,
                'quota_used' => 0,
            ]
        );
    }

    /**
     * حذف سهمیه
     */
    public function deleteQuota(User $user, Center $center): bool
    {
        $quota = $this->getQuota($user, $center);

        if (!$quota) {
            return false;
        }

        if ($quota->quota_used > 0) {
            throw new \RuntimeException('نمی‌توان سهمیه‌ای را که استفاده شده حذف کرد');
        }

        return $quota->delete();
    }
}
