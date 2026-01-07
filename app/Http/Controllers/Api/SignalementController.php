<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SignalementResource;
use App\Http\Resources\SignalementCollection;
use App\Models\Signalement;
use Illuminate\Http\Request;

class SignalementController extends Controller
{
    /**
     * Display a listing of signalements.
     */
    public function index()
    {
        $signalements = Signalement::with(['annonce', 'reporter'])
            ->latest()
            ->paginate(20);

        return new SignalementCollection($signalements);
    }

    /**
     * Store a newly created signalement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'annonce_id'        => 'required|exists:annonces,id',
            'motif'             => 'required|string',
            'commentaire'       => 'nullable|string',
            'reporter_user_id'  => 'nullable|exists:users,id',
        ]);

        $signalement = Signalement::create($validated);

        return new SignalementResource(
            $signalement->load(['annonce', 'reporter'])
        );
    }

    /**
     * Display a single signalement.
     */
    public function show(string $id)
    {
        $signalement = Signalement::with(['annonce', 'reporter'])
            ->findOrFail($id);

        return new SignalementResource($signalement);
    }

    /**
     * Update a specific signalement.
     */
    public function update(Request $request, string $id)
    {
        $signalement = Signalement::findOrFail($id);

        $validated = $request->validate([
            'motif'             => 'sometimes|string',
            'commentaire'       => 'sometimes|string|nullable',
            'processed_by_admin'=> 'sometimes|boolean',
            'action_taken'      => 'sometimes|in:none,disabled,deleted,rejected',
        ]);

        $signalement->update($validated);

        return new SignalementResource(
            $signalement->load(['annonce', 'reporter'])
        );
    }

    /**
     * Remove a signalement.
     */
    public function destroy(string $id)
    {
        $signalement = Signalement::findOrFail($id);
        $signalement->delete();

        return response()->json([
            'message' => 'Signalement supprimé avec succès.',
        ]);
    }
}
