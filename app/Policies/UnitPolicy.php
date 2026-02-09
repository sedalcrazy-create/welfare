<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitPolicy
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
     * مشاهده لیست واحدها
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('units.view');
    }

    /**
     * مشاهده جزئیات واحد
     */
    public function view(User $user, Unit $unit): bool
    {
        return $user->hasPermissionTo('units.view');
    }

    /**
     * ایجاد واحد جدید
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('units.create');
    }

    /**
     * ویرایش واحد
     */
    public function update(User $user, Unit $unit): bool
    {
        return $user->hasPermissionTo('units.edit');
    }

    /**
     * حذف واحد
     */
    public function delete(User $user, Unit $unit): bool
    {
        return $user->hasPermissionTo('units.delete');
    }

    /**
     * تغییر وضعیت واحد
     */
    public function toggleStatus(User $user, Unit $unit): bool
    {
        return $user->hasPermissionTo('units.edit');
    }

    /**
     * ایمپورت واحدها
     */
    public function import(User $user): bool
    {
        return $user->hasPermissionTo('units.create');
    }
}
