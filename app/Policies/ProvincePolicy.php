<?php

namespace App\Policies;

use App\Models\Province;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProvincePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'provincial_admin', 'operator']);
    }

    public function view(User $user, Province $province): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // مدیر استانی فقط استان خودش
        if ($user->hasRole('provincial_admin')) {
            return $user->canManageProvince($province->id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Province $province): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Province $province): bool
    {
        return false; // استان‌ها قابل حذف نیستند
    }
}
