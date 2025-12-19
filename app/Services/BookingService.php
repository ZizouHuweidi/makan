<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Check if a listing is available for the given dates.
     */
    public function isAvailable(Listing $listing, string $startDate, string $endDate): bool
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return !Booking::where('listing_id', $listing->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end->copy()->subSecond()])
                    ->orWhereBetween('end_date', [$start->copy()->addSecond(), $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();
    }

    /**
     * Calculate the total price for a booking.
     */
    public function calculateTotalPrice(Listing $listing, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $nights = (int) $start->diffInDays($end);

        if ($nights <= 0) {
            throw ValidationException::withMessages([
                'dates' => ['Check-out date must be after check-in date.'],
            ]);
        }

        return [
            'nights' => $nights,
            'total_price' => $listing->price_per_night * $nights,
        ];
    }

    /**
     * Create a new booking with all checks.
     */
    public function createBooking(Listing $listing, array $data, $user): Booking
    {
        if (!$listing->is_active) {
            throw ValidationException::withMessages([
                'listing' => ['This listing is not available for booking.'],
            ]);
        }

        if (!$this->isAvailable($listing, $data['start_date'], $data['end_date'])) {
            throw ValidationException::withMessages([
                'dates' => ['The selected dates overlap with an existing booking.'],
            ]);
        }

        $priceData = $this->calculateTotalPrice($listing, $data['start_date'], $data['end_date']);

        return Booking::create([
            'listing_id' => $listing->id,
            'guest_id' => $user->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'nights' => $priceData['nights'],
            'total_price' => $priceData['total_price'],
            'status' => 'pending',
            'guest_notes' => $data['guest_notes'] ?? null,
        ]);
    }
}
