<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     * Anyone can view approved reviews, admins can view all.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Filtering happens in controller
    }

    /**
     * Determine whether the user can view the model.
     * Anyone can view approved reviews, author/admin can view any.
     */
    public function view(?User $user, Review $review): bool
    {
        if ($review->is_approved) {
            return true;
        }

        // Author or admin can view unapproved reviews
        return $user && ($user->id === $review->user_id || $user->hasRole('admin'));
    }

    /**
     * Determine whether the user can create models.
     * Guests can create reviews.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['guest', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     * Only the author can update their review.
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Author can delete their own review, admin/support can moderate.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id
            || $user->hasAnyRole(['admin', 'support']);
    }

    /**
     * Determine whether the user can moderate (approve/reject) reviews.
     * Admin and support can moderate.
     */
    public function moderate(User $user, Review $review): bool
    {
        return $user->hasAnyRole(['admin', 'support']);
    }

    /**
     * Determine whether the user can restore the model.
     * Only admin can restore.
     */
    public function restore(User $user, Review $review): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only admin can force delete.
     */
    public function forceDelete(User $user, Review $review): bool
    {
        return $user->hasRole('admin');
    }
}
