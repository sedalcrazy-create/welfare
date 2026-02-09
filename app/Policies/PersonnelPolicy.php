<?php

namespace App\Policies;

use App\Models\Personnel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonnelPolicy
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
        return $user->hasPermissionTo('personnel.view');
    }

    public function view(User $user, Personnel $personnel): bool
    {
        if ($user->hasPermissionTo('personnel.view')) {
            return true;
        }

        // مدیر استانی فقط پرسنل استان خودش
        if ($user->hasRole('provincial_admin')) {
            return $user->canManageProvince($personnel->province_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('personnel.create');
    }

    public function update(User $user, Personnel $personnel): bool
    {
        return $user->hasPermissionTo('personnel.edit');
    }

    public function delete(User $user, Personnel $personnel): bool
    {
        return $user->hasPermissionTo('personnel.delete');
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo('personnel.import');
    }
}
