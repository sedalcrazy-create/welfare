<?php

namespace App\Policies;

use App\Models\Lottery;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LotteryPolicy
{
    use HandlesAuthorization;

    /**
     * قبل از هر بررسی - super_admin همه دسترسی‌ها را دارد
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null;
    }

    /**
     * مشاهده لیست قرعه‌کشی‌ها
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('lotteries.view');
    }

    /**
     * مشاهده جزئیات قرعه‌کشی
     */
    public function view(User $user, Lottery $lottery): bool
    {
        return $user->hasPermissionTo('lotteries.view');
    }

    /**
     * ایجاد قرعه‌کشی جدید
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('lotteries.create');
    }

    /**
     * ویرایش قرعه‌کشی
     */
    public function update(User $user, Lottery $lottery): bool
    {
        if (!$user->hasPermissionTo('lotteries.edit')) {
            return false;
        }

        // فقط در وضعیت draft و open قابل ویرایش است
        return in_array($lottery->status, [Lottery::STATUS_DRAFT, Lottery::STATUS_OPEN]);
    }

    /**
     * حذف قرعه‌کشی
     */
    public function delete(User $user, Lottery $lottery): bool
    {
        if (!$user->hasPermissionTo('lotteries.create')) {
            return false;
        }

        // فقط در وضعیت draft قابل حذف است
        return $lottery->status === Lottery::STATUS_DRAFT;
    }

    /**
     * اجرای قرعه‌کشی
     */
    public function draw(User $user, Lottery $lottery): bool
    {
        if (!$user->hasPermissionTo('lotteries.draw')) {
            return false;
        }

        // فقط در وضعیت closed و با تاریخ رسیده قابل اجرا است
        return $lottery->status === Lottery::STATUS_CLOSED;
    }

    /**
     * لغو قرعه‌کشی
     */
    public function cancel(User $user, Lottery $lottery): bool
    {
        if (!$user->hasPermissionTo('lotteries.cancel')) {
            return false;
        }

        // در وضعیت completed قابل لغو نیست
        return $lottery->status !== Lottery::STATUS_COMPLETED;
    }

    /**
     * تایید/رد شرکت‌کنندگان
     */
    public function manageEntries(User $user, Lottery $lottery): bool
    {
        // مدیر استانی فقط شرکت‌کنندگان استان خودش
        if ($user->hasRole('provincial_admin')) {
            return $user->hasPermissionTo('entries.approve') || $user->hasPermissionTo('entries.reject');
        }

        return $user->hasPermissionTo('entries.approve') || $user->hasPermissionTo('entries.reject');
    }
}
