<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    /**
     * Determine whether the user can view any models.
     * Anyone can view listings (public index).
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Anyone can view a listing if it's active, or the owner/admin can view any.
     */
    public function view(?User $user, Listing $listing): bool
    {
        if ($listing->is_active) {
            return true;
        }

        // Owner or admin can view inactive listings
        return $user && ($user->id === $listing->host_id || $user->hasRole('admin'));
    }

    /**
     * Determine whether the user can create models.
     * Hosts and admins can create listings.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['host', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     * Only the owner or admin can update.
     */
    public function update(User $user, Listing $listing): bool
    {
        return $user->id === $listing->host_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     * Only the owner or admin can delete.
     */
    public function delete(User $user, Listing $listing): bool
    {
        return $user->id === $listing->host_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     * Only admin can restore.
     */
    public function restore(User $user, Listing $listing): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only admin can force delete.
     */
    public function forceDelete(User $user, Listing $listing): bool
    {
        return $user->hasRole('admin');
    }
}
