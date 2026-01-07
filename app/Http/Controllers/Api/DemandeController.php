<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\Annonce;
use App\Http\Resources\DemandeResource;
use App\Http\Resources\DemandeCollection;
use App\Models\Notification;

class DemandeController extends Controller
{
    /**
     * Display a listing of demandes.
     *
     * - Si "annonce_id" est fourni → demandes pour une annonce
     * - Si utilisateur authentifié → ses propres demandes reçues
     */
    public function index(Request $request)
    {
        if ($request->filled('annonce_id')) {
            $demandes = Demande::where('annonce_id', $request->annonce_id)
                ->orderBy('created_at', 'desc')
                ->get();

            return new DemandeCollection($demandes);
        }

        // Demandes reçues par le propriétaire (agent)
        $demandes = Demande::whereHas('annonce', function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return new DemandeCollection($demandes);
    }

    /**
     * Store a newly created demande (achat / location / reservation).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'annonce_id'      => 'required|exists:annonces,id',
            'type'            => 'required|in:achat,location,reservation',
            'requester_name'  => 'required|string|max:120',
            'requester_email' => 'nullable|email|max:150',
            'requester_phone' => 'nullable|string|max:30',
            'message'         => 'nullable|string|max:1500',
        ]);

        $annonce = Annonce::findOrFail($validated['annonce_id']);

        // Create demande
        $demande = Demande::create($validated);

        // Notify annonce owner
        Notification::create([
            'user_id' => $annonce->owner_id,
            'type'    => 'demande',
            'payload' => [
                'annonce_id' => $annonce->id,
                'demande_id' => $demande->id,
                'type'       => $demande->type,
                'from'       => $demande->requester_name,
            ],
        ]);

        return new DemandeResource($demande);
    }

    /**
     * Display the specified demande.
     */
    public function show(string $id)
    {
        $demande = Demande::findOrFail($id);
        return new DemandeResource($demande);
    }

    /**
     * Update the specified demande.
     *
     * Seul un administrateur ou le propriétaire de l'annonce peut modifier.
     */
    public function update(Request $request, string $id)
    {
        $demande = Demande::findOrFail($id);

        // Vérification autorisation
        if ($demande->annonce->owner_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'nullable|in:pending,accepted,rejected'
        ]);

        $demande->update($validated);

        return new DemandeResource($demande);
    }

    /**
     * Remove the specified demande from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $demande = Demande::findOrFail($id);

        // Vérification autorisation
        if ($demande->annonce->owner_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $demande->delete();

        return response()->json([
            'message' => 'Demande supprimée avec succès.'
        ]);
    }
}
