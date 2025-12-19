<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $host = User::where('email', 'host@makan.com')->first();

        if (! $host) {
            $this->command->warn('Host user not found. Please run RolePermissionSeeder first.');

            return;
        }

        $listings = [
            [
                'title' => 'Cozy Apartment in Downtown',
                'description' => 'Beautiful 2-bedroom apartment in the heart of the city. Close to restaurants, shops, and public transport.',
                'price_per_night' => 75.00,
                'city' => 'Tripoli',
                'address' => '123 Main Street',
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'amenities' => ['wifi', 'parking', 'kitchen', 'air-conditioning'],
            ],
            [
                'title' => 'Luxury Villa with Pool',
                'description' => 'Spacious 4-bedroom villa with private pool and garden. Perfect for families or groups.',
                'price_per_night' => 200.00,
                'city' => 'Beirut',
                'address' => '456 Beach Road',
                'max_guests' => 8,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'amenities' => ['wifi', 'parking', 'kitchen', 'pool', 'tv', 'washer', 'dryer'],
            ],
            [
                'title' => 'Modern Studio Apartment',
                'description' => 'Compact and modern studio apartment perfect for solo travelers or couples.',
                'price_per_night' => 50.00,
                'city' => 'Tripoli',
                'address' => '789 Market Square',
                'max_guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'amenities' => ['wifi', 'kitchen', 'air-conditioning', 'tv'],
            ],
            [
                'title' => 'Beachfront Condo',
                'description' => 'Stunning beachfront condo with ocean views. Steps away from the beach.',
                'price_per_night' => 150.00,
                'city' => 'Byblos',
                'address' => '321 Coastal Drive',
                'max_guests' => 6,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'amenities' => ['wifi', 'parking', 'kitchen', 'air-conditioning', 'tv', 'washer'],
            ],
            [
                'title' => 'Mountain Cabin Retreat',
                'description' => 'Peaceful mountain cabin surrounded by nature. Ideal for a quiet getaway.',
                'price_per_night' => 90.00,
                'city' => 'Bcharre',
                'address' => '654 Mountain View',
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'amenities' => ['wifi', 'kitchen', 'heating', 'tv', 'pet-friendly'],
            ],
        ];

        foreach ($listings as $listingData) {
            $amenities = $listingData['amenities'];
            unset($listingData['amenities']);

            $listing = Listing::create(array_merge($listingData, [
                'host_id' => $host->id,
                'is_active' => true,
            ]));

            // Attach amenities
            $amenityIds = Amenity::whereIn('slug', $amenities)->pluck('id');
            $listing->amenities()->attach($amenityIds);
        }
    }
}
