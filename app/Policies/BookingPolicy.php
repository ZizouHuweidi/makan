<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     * Users can view their own bookings (as guest or host), admins can view all.
     */
    public function viewAny(User $user): bool
    {
        return true; // Filtering happens in controller
    }

    /**
     * Determine whether the user can view the model.
     * Guest, host of the listing, or admin can view.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->guest_id
            || $user->id === $booking->listing->host_id
            || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     * Guests and admins can create bookings.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['guest', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     * Only admin can update bookings directly (status changes handled separately).
     */
    public function update(User $user, Booking $booking): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can change booking status.
     * Host of the listing or admin can change status.
     */
    public function changeStatus(User $user, Booking $booking): bool
    {
        return $user->id === $booking->listing->host_id
            || $user->hasAnyRole(['admin', 'support']);
    }

    /**
     * Determine whether the user can delete the model.
     * Guest can cancel their own booking, admin can delete any.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Guest can cancel their own pending booking
        if ($user->id === $booking->guest_id && $booking->status === 'pending') {
            return true;
        }

        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     * Only admin can restore.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only admin can force delete.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return $user->hasRole('admin');
    }
}
