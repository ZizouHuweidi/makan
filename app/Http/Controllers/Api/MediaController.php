<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Store a newly created media for a listing.
     */
    public function store(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('create', [Media::class, Listing::class, $listing->id]);

        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov|max:10240',
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media/listings/'.$listing->id, $filename, 'public');

        $media = Media::create([
            'mediable_type' => Listing::class,
            'mediable_id' => $listing->id,
            'file_name' => $filename,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'disk' => 'public',
            'collection' => 'listing-media',
        ]);

        return response()->json([
            'id' => $media->id,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'url' => $media->url,
        ], 201);
    }

    /**
     * Display the specified media.
     */
    public function show(Media $media): JsonResponse
    {
        return response()->json([
            'id' => $media->id,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'file_size' => $media->file_size,
            'url' => $media->url,
        ]);
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(Request $request, Media $media): JsonResponse
    {
        $this->authorize('delete', $media);

        // Delete file from storage
        Storage::disk($media->disk ?? 'public')->delete($media->file_path);

        $media->delete();

        return response()->json(['message' => 'Media deleted successfully'], 200);
    }
}
