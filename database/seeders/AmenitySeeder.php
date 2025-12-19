<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'WiFi', 'slug' => 'wifi', 'icon' => 'wifi'],
            ['name' => 'Parking', 'slug' => 'parking', 'icon' => 'car'],
            ['name' => 'Kitchen', 'slug' => 'kitchen', 'icon' => 'utensils'],
            ['name' => 'Air Conditioning', 'slug' => 'air-conditioning', 'icon' => 'snowflake'],
            ['name' => 'Heating', 'slug' => 'heating', 'icon' => 'thermometer'],
            ['name' => 'TV', 'slug' => 'tv', 'icon' => 'tv'],
            ['name' => 'Washer', 'slug' => 'washer', 'icon' => 'tshirt'],
            ['name' => 'Dryer', 'slug' => 'dryer', 'icon' => 'wind'],
            ['name' => 'Pool', 'slug' => 'pool', 'icon' => 'swimming-pool'],
            ['name' => 'Gym', 'slug' => 'gym', 'icon' => 'dumbbell'],
            ['name' => 'Pet Friendly', 'slug' => 'pet-friendly', 'icon' => 'paw'],
            ['name' => 'Smoking Allowed', 'slug' => 'smoking-allowed', 'icon' => 'smoking'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(
                ['slug' => $amenity['slug']],
                $amenity
            );
        }
    }
}
