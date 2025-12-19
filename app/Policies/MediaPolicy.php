<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use App\Models\Listing;

class MediaPolicy
{
    /**
     * Determine whether any user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Media $media): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create (upload) media.
     */
    public function create(User $user, string $mediableType, string $mediableId): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($mediableType === Listing::class) {
            $listing = Listing::findOrFail($mediableId);
            return $user->id === $listing->host_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user, Media $media): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($media->mediable_type === Listing::class) {
            $listing = Listing::findOrFail($media->mediable_id);
            return $user->id === $listing->host_id;
        }

        return false;
    }
}
