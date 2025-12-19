<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
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

        // Create some past bookings
        foreach ($listings->take(2) as $listing) {
            $startDate = Carbon::now()->subDays(30);
            $endDate = Carbon::now()->subDays(25);
            $nights = (int) $startDate->diffInDays($endDate);

            Booking::create([
                'listing_id' => $listing->id,
                'guest_id' => $guest->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'nights' => $nights,
                'total_price' => $listing->price_per_night * $nights,
                'status' => 'completed',
            ]);
        }

        // Create some upcoming bookings
        foreach ($listings->take(2) as $listing) {
            $startDate = Carbon::now()->addDays(10);
            $endDate = Carbon::now()->addDays(15);
            $nights = (int) $startDate->diffInDays($endDate);

            Booking::create([
                'listing_id' => $listing->id,
                'guest_id' => $guest->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'nights' => $nights,
                'total_price' => $listing->price_per_night * $nights,
                'status' => 'confirmed',
            ]);
        }

        // Create a pending booking
        $listing = $listings->first();
        $startDate = Carbon::now()->addDays(20);
        $endDate = Carbon::now()->addDays(23);
        $nights = (int) $startDate->diffInDays($endDate);

        Booking::create([
            'listing_id' => $listing->id,
            'guest_id' => $guest->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'nights' => $nights,
            'total_price' => $listing->price_per_night * $nights,
            'status' => 'pending',
            'guest_notes' => 'Looking forward to my stay!',
        ]);
    }
}
