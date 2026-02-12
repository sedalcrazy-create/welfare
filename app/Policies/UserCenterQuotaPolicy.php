<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserCenterQuota;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserCenterQuotaPolicy
{
    use HandlesAuthorization;

    /**
     * Before: super_admin can do everything
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null;
    }

    /**
     * Determine if user can view any quotas
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine if user can view a specific quota
     */
    public function view(User $user, UserCenterQuota $quota): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine if user can allocate quotas
     */
    public function allocate(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine if user can update quotas
     */
    public function update(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine if user can reset quotas (only super_admin)
     */
    public function reset(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if user can delete quotas
     */
    public function delete(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
