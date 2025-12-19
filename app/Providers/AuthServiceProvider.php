<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\Review;
use App\Policies\BookingPolicy;
use App\Policies\ListingPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Listing::class => ListingPolicy::class,
        Booking::class => BookingPolicy::class,
        Review::class => ReviewPolicy::class,
        \App\Models\Amenity::class => \App\Policies\AmenityPolicy::class,
        \App\Models\Media::class => \App\Policies\MediaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
