<?php

namespace App\Models;

use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Listing extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Invalidate cache when listing is created, updated, or deleted
        static::saved(function () {
            Cache::flush(); // In production, use tags: Cache::tags(['listings'])->flush();
        });

        static::deleted(function () {
            Cache::flush(); // In production, use tags: Cache::tags(['listings'])->flush();
        });
    }

    protected $fillable = [
        'host_id',
        'title',
        'description',
        'price_per_night',
        'city',
        'address',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'rating',
        'review_count',
        'is_active',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'max_guests' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'review_count' => 'integer',
    ];

    /**
     * Get the host (user) that owns the listing.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get the amenities for the listing.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    /**
     * Get the bookings for the listing.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all reviews for the listing.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Get all media attachments for the listing.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Scope a query to filter by city.
     */
    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'ilike', "%{$city}%");
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) {
            $query->where('price_per_night', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price_per_night', '<=', $max);
        }

        return $query;
    }

    /**
     * Scope a query to filter by amenities.
     */
    public function scopeWithAmenities(Builder $query, array $amenityIds): Builder
    {
        return $query->whereHas('amenities', function ($q) use ($amenityIds) {
            $q->whereIn('amenities.id', $amenityIds);
        }, '=', count($amenityIds));
    }

    /**
     * Scope a query to filter by date availability.
     */
    public function scopeAvailableBetween(Builder $query, string $checkin, string $checkout): Builder
    {
        $checkinDate = Carbon::parse($checkin);
        $checkoutDate = Carbon::parse($checkout);

        return $query->whereDoesntHave('bookings', function ($q) use ($checkinDate, $checkoutDate) {
            $q->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($checkinDate, $checkoutDate) {
                    $query->whereBetween('start_date', [$checkinDate, $checkoutDate])
                        ->orWhereBetween('end_date', [$checkinDate, $checkoutDate])
                        ->orWhere(function ($q) use ($checkinDate, $checkoutDate) {
                            $q->where('start_date', '<=', $checkinDate)
                                ->where('end_date', '>=', $checkoutDate);
                        });
                });
        });
    }

    /**
     * Scope a query to filter by minimum rating.
     */
    public function scopeMinRating(Builder $query, float $rating): Builder
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope a query to filter by minimum guests capacity.
     */
    public function scopeMinGuests(Builder $query, int $guests): Builder
    {
        return $query->where('max_guests', '>=', $guests);
    }

    /**
     * Scope a query to sort listings.
     */
    public function scopeSortBy(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price_per_night', 'asc'),
            'price_desc' => $query->orderBy('price_per_night', 'desc'),
            'rating' => $query->orderBy('rating', 'desc')->orderBy('review_count', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }
}
