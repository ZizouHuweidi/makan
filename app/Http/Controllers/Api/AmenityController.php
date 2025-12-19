<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $amenities = Amenity::orderBy('name')->get();

        return response()->json($amenities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Amenity::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:amenities',
            'description' => 'nullable|string|max:1000',
        ]);

        $amenity = Amenity::create($validated);

        return response()->json($amenity, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Amenity $amenity): JsonResponse
    {
        return response()->json($amenity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Amenity $amenity): JsonResponse
    {
        $this->authorize('update', $amenity);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:amenities,slug,'.$amenity->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $amenity->update($validated);

        return response()->json($amenity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Amenity $amenity): JsonResponse
    {
        $this->authorize('destroy', $amenity);

        $amenity->delete();

        return response()->json(['message' => 'Amenity deleted successfully'], 200);
    }
}
