<?php

namespace App\Policies;

use App\Models\Center;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CenterPolicy
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
     * مشاهده لیست مراکز
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('centers.view');
    }

    /**
     * مشاهده جزئیات مرکز
     */
    public function view(User $user, Center $center): bool
    {
        return $user->hasPermissionTo('centers.view');
    }

    /**
     * ایجاد مرکز جدید
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('centers.create');
    }

    /**
     * ویرایش مرکز
     */
    public function update(User $user, Center $center): bool
    {
        return $user->hasPermissionTo('centers.edit');
    }

    /**
     * حذف مرکز
     */
    public function delete(User $user, Center $center): bool
    {
        return $user->hasPermissionTo('centers.delete');
    }

    /**
     * تغییر وضعیت مرکز
     */
    public function toggleStatus(User $user, Center $center): bool
    {
        return $user->hasPermissionTo('centers.edit');
    }
}
