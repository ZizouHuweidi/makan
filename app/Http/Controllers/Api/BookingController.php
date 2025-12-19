<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingStoreRequest;
use App\Jobs\SendBookingCancellationNotification;
use App\Jobs\SendBookingConfirmationNotification;
use App\Models\Booking;
use App\Models\Listing;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Booking::with(['listing', 'guest']);

        // Filter by user role
        if ($user->hasRole('admin')) {
            // Admin sees all bookings
        } elseif ($user->hasRole('host')) {
            // Host sees bookings for their listings
            $query->whereHas('listing', function ($q) use ($user) {
                $q->where('host_id', $user->id);
            });
        } else {
            // Guest sees only their own bookings
            $query->where('guest_id', $user->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('listing_id')) {
            $query->where('listing_id', $request->get('listing_id'));
        }

        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->get('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->get('end_date'));
        }

        $perPage = $request->get('per_page', 15);
        $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookingStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $listing = Listing::findOrFail($validated['listing_id']);

        $booking = $this->bookingService->createBooking($listing, $validated, $request->user());

        $booking->load(['listing', 'guest']);

        // Dispatch confirmation notification job
        SendBookingConfirmationNotification::dispatch($booking);

        return response()->json($booking, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load(['listing.host', 'listing.amenities', 'guest']);

        return response()->json($booking);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'guest_notes' => 'sometimes|string|max:1000',
        ]);

        $booking->update($validated);
        $booking->load(['listing', 'guest']);

        return response()->json($booking);
    }

    /**
     * Change booking status.
     */
    public function changeStatus(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('changeStatus', $booking);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $oldStatus = $booking->status;
        $booking->update(['status' => $validated['status']]);
        $booking->load(['listing', 'guest']);

        // Dispatch notifications based on status change
        if ($validated['status'] === 'confirmed' && $oldStatus !== 'confirmed') {
            SendBookingConfirmationNotification::dispatch($booking->fresh());
        } elseif ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            SendBookingCancellationNotification::dispatch($booking->fresh());
        }

        return response()->json($booking);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking): JsonResponse
    {
        $this->authorize('delete', $booking);

        // If booking is being cancelled (not just deleted), send notification
        if ($booking->status !== 'cancelled') {
            $booking->update(['status' => 'cancelled']);
            SendBookingCancellationNotification::dispatch($booking->fresh());
        }

        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully'], 200);
    }
}
