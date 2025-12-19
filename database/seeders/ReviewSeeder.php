<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guest = User::where('email', 'guest@makan.com')->first();
        $listings = Listing::all();

        if (! $guest || $listings->isEmpty()) {
            $this->command->warn('Guest user or listings not found. Please run RolePermissionSeeder and ListingSeeder first.');

            return;
        }

        $reviews = [
            ['rating' => 5, 'comment' => 'Amazing place! Very clean and well-located.'],
            ['rating' => 4, 'comment' => 'Great experience, would definitely stay again.'],
            ['rating' => 5, 'comment' => 'Perfect location and excellent host. Highly recommended!'],
            ['rating' => 4, 'comment' => 'Nice place, had everything we needed.'],
            ['rating' => 5, 'comment' => 'Exceeded expectations! Beautiful property.'],
        ];

        foreach ($listings->take(5) as $index => $listing) {
            if (isset($reviews[$index])) {
                Review::create([
                    'user_id' => $guest->id,
                    'reviewable_type' => Listing::class,
                    'reviewable_id' => $listing->id,
                    'rating' => $reviews[$index]['rating'],
                    'comment' => $reviews[$index]['comment'],
                    'is_approved' => true,
                ]);
            }
        }

        // Update listing ratings
        foreach ($listings as $listing) {
            $approvedReviews = Review::where('reviewable_type', Listing::class)
                ->where('reviewable_id', $listing->id)
                ->where('is_approved', true)
                ->get();

            if ($approvedReviews->isNotEmpty()) {
                $listing->update([
                    'rating' => round($approvedReviews->avg('rating'), 2),
                    'review_count' => $approvedReviews->count(),
                ]);
            }
        }
    }
}
