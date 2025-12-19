<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingStoreRequest;
use App\Http\Requests\ListingUpdateRequest;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Build cache key from query parameters
        $cacheKey = 'listings:index:'.md5(json_encode($request->all()));

        // Cache for 15 minutes (900 seconds)
        $listings = Cache::remember($cacheKey, 900, function () use ($request) {
            $query = Listing::with(['host', 'amenities'])->where('is_active', true);

            // City filter
            if ($request->has('city')) {
                $query->inCity($request->get('city'));
            }

            // Price range filter
            if ($request->has('price_min') || $request->has('price_max')) {
                $query->priceRange(
                    $request->get('price_min') ? (float) $request->get('price_min') : null,
                    $request->get('price_max') ? (float) $request->get('price_max') : null
                );
            }

            // Amenities filter
            if ($request->has('amenities')) {
                $amenities = is_array($request->get('amenities'))
                    ? $request->get('amenities')
                    : explode(',', $request->get('amenities'));
                $query->withAmenities($amenities);
            }

            // Date availability filter
            if ($request->has('checkin') && $request->has('checkout')) {
                $query->availableBetween($request->get('checkin'), $request->get('checkout'));
            }

            // Rating filter
            if ($request->has('rating')) {
                $query->minRating((float) $request->get('rating'));
            }

            // Guests filter
            if ($request->has('guests')) {
                $query->minGuests((int) $request->get('guests'));
            }

            // Sorting
            $sort = $request->get('sort', 'newest');
            $query->sortBy($sort);

            // Pagination
            $perPage = $request->get('per_page', 15);

            return $query->paginate($perPage);
        });

        return response()->json($listings);
    }

    /**
     * Invalidate listings cache.
     */
    private function invalidateListingsCache(): void
    {
        // Clear all listing index cache keys
        Cache::flush(); // In production, use tags: Cache::tags(['listings'])->flush();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ListingStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $listing = $request->user()->listings()->create($validated);

        if (isset($validated['amenities'])) {
            $listing->amenities()->attach($validated['amenities']);
        }

        $listing->load(['host', 'amenities']);

        // Invalidate cache when new listing is created
        $this->invalidateListingsCache();

        return response()->json($listing, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $listing): JsonResponse
    {
        $this->authorize('view', $listing);

        $listing->load(['host', 'amenities', 'reviews.user', 'media']);

        return response()->json($listing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ListingUpdateRequest $request, Listing $listing): JsonResponse
    {
        $validated = $request->validated();

        $listing->update($validated);

        if (isset($validated['amenities'])) {
            $listing->amenities()->sync($validated['amenities']);
        }

        $listing->load(['host', 'amenities']);

        // Invalidate cache when listing is updated
        $this->invalidateListingsCache();

        return response()->json($listing);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);

        $listing->delete();

        // Invalidate cache when listing is deleted
        $this->invalidateListingsCache();

        return response()->json(['message' => 'Listing deleted successfully'], 200);
    }
}
