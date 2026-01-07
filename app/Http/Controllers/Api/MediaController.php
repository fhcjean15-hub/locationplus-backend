<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;
use App\Models\Annonce;
use App\Http\Resources\MediaResource;
use App\Http\Resources\MediaCollection;

class MediaController extends Controller
{
    /**
     * Display a listing of medias for a specific annonce.
     */
    public function index(Request $request)
    {
        $request->validate([
            'annonce_id' => 'required|exists:annonces,id'
        ]);

        $medias = Media::where('annonce_id', $request->annonce_id)
                        ->orderBy('order_index')
                        ->get();

        return new MediaCollection($medias);
    }

    /**
     * Store newly uploaded medias for an annonce.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'annonce_id' => 'required|exists:annonces,id',
            'medias.*'   => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:20480'
        ]);

        $annonce = Annonce::findOrFail($validated['annonce_id']);

        // Only the owner can add media
        if ($annonce->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $createdMedias = [];

        foreach ($request->file('medias') as $mediaFile) {
            $path = $mediaFile->store('annonces', 'public');

            // Determine type from MIME
            $mime = $mediaFile->getClientMimeType();
            $type = str_starts_with($mime, 'video') ? 'video' : 'image';

            $media = Media::create([
                'annonce_id'  => $annonce->id,
                'url'         => $path,
                'type'        => $type,
                'order_index' => Media::where('annonce_id', $annonce->id)->count(),
            ]);

            $createdMedias[] = $media;
        }

        return new MediaCollection(collect($createdMedias));
    }

    /**
     * Display a specific media.
     */
    public function show(string $id)
    {
        $media = Media::findOrFail($id);
        return new MediaResource($media);
    }

    /**
     * Update a media (reorder or replace file).
     */
    public function update(Request $request, string $id)
    {
        $media = Media::findOrFail($id);

        // Only owner of annonce can update media
        if ($media->annonce->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'order_index' => 'nullable|integer',
            'file'        => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:20480'
        ]);

        // Update order_index
        if ($request->has('order_index')) {
            $media->order_index = $validated['order_index'];
        }

        // Replace media file
        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($media->url);

            $file = $request->file('file');
            $path = $file->store('annonces', 'public');

            $mime = $file->getClientMimeType();
            $type = str_starts_with($mime, 'video') ? 'video' : 'image';

            $media->url  = $path;
            $media->type = $type;
        }

        $media->save();

        return new MediaResource($media);
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $media = Media::findOrFail($id);

        // Only owner can delete
        if ($media->annonce->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Storage::disk('public')->delete($media->url);
        $media->delete();

        return response()->json([
            'message' => "Média supprimé avec succès."
        ]);
    }
}
