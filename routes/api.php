<?php

use App\Http\Controllers\Api\AmenityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\ReviewController;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ===================
// Auth Routes
// ===================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ===================
// Public Routes
// ===================

// Listings
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{listing}', [ListingController::class, 'show']);

// Amenities
Route::get('/amenities', [AmenityController::class, 'index']);
Route::get('/amenities/{amenity}', [AmenityController::class, 'show']);

// Media
Route::get('/media/{media}', [MediaController::class, 'show']);

// Reviews for a listing (public)
Route::get('/listings/{listing}/reviews', [ReviewController::class, 'indexForListing']);

// ===================
// Protected Routes
// ===================
Route::middleware('auth:sanctum')->group(function () {
    // Listings
    Route::post('/listings', [ListingController::class, 'store']);
    Route::put('/listings/{listing}', [ListingController::class, 'update']);
    Route::delete('/listings/{listing}', [ListingController::class, 'destroy']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'changeStatus']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::patch('/reviews/{review}/moderate', [ReviewController::class, 'moderate']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // Create review for a listing (authenticated)
    Route::post('/listings/{listing}/reviews', [ReviewController::class, 'storeForListing']);

    // Amenities (admin only)
    Route::post('/amenities', [AmenityController::class, 'store']);
    Route::put('/amenities/{amenity}', [AmenityController::class, 'update']);
    Route::delete('/amenities/{amenity}', [AmenityController::class, 'destroy']);

    // Media
    Route::post('/listings/{listing}/media', [MediaController::class, 'store']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
});
