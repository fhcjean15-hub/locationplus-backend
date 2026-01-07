<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Annonce;
use App\Models\Media;
use App\Http\Resources\AnnonceResource;
use App\Http\Resources\AnnonceCollection;

class AnnonceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Annonce::query()->with('medias', 'owner');

        // Filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Pagination
        $annonces = $query->latest()->paginate(10);

        return new AnnonceCollection($annonces);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Limite d'annonces selon la catégorie du compte
        if ($user->accountCategory && $user->accountCategory->max_annonces > 0) {
            $countAnnonce = Annonce::where('owner_id', $user->id)->count();

            if ($countAnnonce >= $user->accountCategory->max_annonces) {
                return response()->json([
                    'message' => "Limite d'annonces atteinte pour votre catégorie de compte."
                ], 403);
            }
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:200',
            'description'   => 'nullable|string',
            'price'         => 'nullable|numeric',
            'category_id'   => 'required|exists:categories,id',
            'location_text' => 'nullable|string|max:255',
            'lat'           => 'nullable|numeric',
            'lng'           => 'nullable|numeric',
            'data_json'     => 'nullable|array',
            'status'        => 'nullable|in:active,inactive,deleted,flagged',
            'medias.*'      => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:20480'
        ]);

        $validated['owner_id'] = $user->id;

        if (isset($validated['data_json'])) {
            $validated['data_json'] = json_encode($validated['data_json']);
        }

        // Create annonce
        $annonce = Annonce::create($validated);

        // Upload medias
        if ($request->hasFile('medias')) {
            foreach ($request->file('medias') as $file) {
                $path = $file->store('annonces', 'public');

                Media::create([
                    'annonce_id' => $annonce->id,
                    'url'        => $path,
                    'type'       => $file->getClientMimeType(),
                ]);
            }
        }

        return new AnnonceResource($annonce->load('medias', 'owner'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $annonce = Annonce::with('medias', 'owner')->findOrFail($id);
        return new AnnonceResource($annonce);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $annonce = Annonce::findOrFail($id);

        if ($annonce->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'         => 'sometimes|string|max:200',
            'description'   => 'sometimes|string',
            'price'         => 'sometimes|numeric',
            'category_id'   => 'sometimes|exists:categories,id',
            'location_text' => 'sometimes|string|max:255',
            'lat'           => 'sometimes|numeric',
            'lng'           => 'sometimes|numeric',
            'data_json'     => 'sometimes|array',
            'status'        => 'sometimes|in:active,inactive,deleted,flagged',
            'medias.*'      => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:20480'
        ]);

        if (isset($validated['data_json'])) {
            $validated['data_json'] = json_encode($validated['data_json']);
        }

        $annonce->update($validated);

        // Ajout nouveaux médias
        if ($request->hasFile('medias')) {
            foreach ($request->file('medias') as $file) {
                $path = $file->store('annonces', 'public');

                Media::create([
                    'annonce_id' => $annonce->id,
                    'url'        => $path,
                    'type'       => $file->getClientMimeType(),
                ]);
            }
        }

        return new AnnonceResource($annonce->load('medias', 'owner'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $annonce = Annonce::findOrFail($id);

        if ($annonce->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // delete medias
        foreach ($annonce->medias as $media) {
            Storage::disk('public')->delete($media->url);
            $media->delete();
        }

        $annonce->delete();

        return response()->json([
            'message' => "Annonce supprimée avec succès."
        ]);
    }
}
