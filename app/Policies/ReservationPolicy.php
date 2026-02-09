<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
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
        return $user->hasPermissionTo('reservations.view');
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->hasPermissionTo('reservations.view')) {
            return true;
        }

        // مدیر استانی فقط رزروهای استان خودش
        if ($user->hasRole('provincial_admin')) {
            return $user->canManageProvince($reservation->personnel->province_id ?? 0);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('reservations.create');
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->hasPermissionTo('reservations.edit');
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->hasPermissionTo('reservations.cancel') &&
               $reservation->status === Reservation::STATUS_PENDING;
    }

    public function checkIn(User $user, Reservation $reservation): bool
    {
        return $user->hasPermissionTo('reservations.check_in') &&
               $reservation->status === Reservation::STATUS_CONFIRMED;
    }

    public function checkOut(User $user, Reservation $reservation): bool
    {
        return $user->hasPermissionTo('reservations.check_out') &&
               $reservation->status === Reservation::STATUS_CHECKED_IN;
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        return $user->hasPermissionTo('reservations.cancel') &&
               in_array($reservation->status, [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED]);
    }
}
