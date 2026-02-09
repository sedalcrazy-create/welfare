<?php

namespace App\Policies;

use App\Models\Period;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PeriodPolicy
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
     * مشاهده لیست دوره‌ها
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('periods.view');
    }

    /**
     * مشاهده جزئیات دوره
     */
    public function view(User $user, Period $period): bool
    {
        return $user->hasPermissionTo('periods.view');
    }

    /**
     * ایجاد دوره جدید
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('periods.create');
    }

    /**
     * ویرایش دوره
     */
    public function update(User $user, Period $period): bool
    {
        return $user->hasPermissionTo('periods.edit');
    }

    /**
     * حذف دوره
     */
    public function delete(User $user, Period $period): bool
    {
        return $user->hasPermissionTo('periods.delete');
    }

    /**
     * تغییر وضعیت دوره
     */
    public function changeStatus(User $user, Period $period): bool
    {
        return $user->hasPermissionTo('periods.edit');
    }
}
