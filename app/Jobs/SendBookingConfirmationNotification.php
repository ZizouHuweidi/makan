<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmationNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $booking = $this->booking->load(['guest', 'listing.host']);

        // Log notification (in production, send email)
        Log::info('Booking confirmation notification', [
            'booking_id' => $booking->id,
            'guest_email' => $booking->guest->email,
            'host_email' => $booking->listing->host->email,
            'listing_title' => $booking->listing->title,
            'start_date' => $booking->start_date,
            'end_date' => $booking->end_date,
        ]);

        // TODO: Send email to guest and host
        // Mail::to($booking->guest->email)->send(new BookingConfirmationMail($booking));
        // Mail::to($booking->listing->host->email)->send(new NewBookingNotificationMail($booking));
    }
}
