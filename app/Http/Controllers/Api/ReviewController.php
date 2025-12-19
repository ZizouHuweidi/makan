<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewStoreRequest;
use App\Http\Requests\ReviewUpdateRequest;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user']);

        // Filter by reviewable type and id
        if ($request->has('reviewable_type') && $request->has('reviewable_id')) {
            $query->where('reviewable_type', $request->get('reviewable_type'))
                ->where('reviewable_id', $request->get('reviewable_id'));
        }

        // By default, only show approved reviews (unless admin)
        if (! $request->user() || ! $request->user()->hasRole('admin')) {
            $query->where('is_approved', true);
        }

        $perPage = $request->get('per_page', 15);
        $reviews = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Verify the reviewable model exists
        $reviewable = $validated['reviewable_type']::findOrFail($validated['reviewable_id']);

        // Check if user already reviewed this item
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('reviewable_type', $validated['reviewable_type'])
            ->where('reviewable_id', $validated['reviewable_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this item.',
                'errors' => [
                    'reviewable' => ['A review for this item already exists.'],
                ],
            ], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'reviewable_type' => $validated['reviewable_type'],
            'reviewable_id' => $validated['reviewable_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => true, // Auto-approve for now
        ]);

        // Update rating if reviewable is a Listing
        if ($review->reviewable_type === Listing::class) {
            $this->updateListingRating($review->reviewable_id);
        }

        $review->load(['user', 'reviewable']);

        return response()->json($review, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review): JsonResponse
    {
        $this->authorize('view', $review);

        $review->load(['user', 'reviewable']);

        return response()->json($review);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReviewUpdateRequest $request, Review $review): JsonResponse
    {
        $validated = $request->validated();

        $review->update($validated);

        // Update rating if reviewable is a Listing
        if ($review->reviewable_type === Listing::class) {
            $this->updateListingRating($review->reviewable_id);
        }

        $review->load(['user', 'reviewable']);

        return response()->json($review);
    }

    /**
     * Moderate a review (approve/reject).
     */
    public function moderate(Request $request, Review $review): JsonResponse
    {
        $this->authorize('moderate', $review);

        $validated = $request->validate([
            'is_approved' => 'required|boolean',
        ]);

        $review->update(['is_approved' => $validated['is_approved']]);

        // Update rating if reviewable is a Listing
        if ($review->reviewable_type === Listing::class) {
            $this->updateListingRating($review->reviewable_id);
        }

        $review->load(['user', 'reviewable']);

        return response()->json($review);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $reviewableId = $review->reviewable_id;
        $reviewableType = $review->reviewable_type;

        $review->delete();

        // Update rating if reviewable is a Listing
        if ($reviewableType === Listing::class) {
            $this->updateListingRating($reviewableId);
        }

        return response()->json(['message' => 'Review deleted successfully'], 200);
    }

    /**
     * Display listing reviews.
     */
    public function indexForListing(Listing $listing): JsonResponse
    {
        $reviews = Review::with(['user'])
            ->where('reviewable_type', Listing::class)
            ->where('reviewable_id', $listing->id)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(request()->get('per_page', 15));

        return response()->json($reviews);
    }

    /**
     * Store listing review.
     */
    public function storeForListing(ReviewStoreRequest $request, Listing $listing): JsonResponse
    {
        // Inject listing data into request for validation/store
        $request->merge([
            'reviewable_type' => Listing::class,
            'reviewable_id' => $listing->id,
        ]);

        return $this->store($request);
    }

    /**
     * Update listing rating based on approved reviews.
     */
    private function updateListingRating(string $listingId): void
    {
        $listing = Listing::findOrFail($listingId);

        $approvedReviews = Review::where('reviewable_type', Listing::class)
            ->where('reviewable_id', $listingId)
            ->where('is_approved', true)
            ->get();

        if ($approvedReviews->isEmpty()) {
            $listing->update([
                'rating' => 0,
                'review_count' => 0,
            ]);
        } else {
            $averageRating = $approvedReviews->avg('rating');
            $listing->update([
                'rating' => round($averageRating, 2),
                'review_count' => $approvedReviews->count(),
            ]);
        }
    }
}
