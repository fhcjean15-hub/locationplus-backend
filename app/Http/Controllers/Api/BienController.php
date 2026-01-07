<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Http\Resources\BienResource;
use App\Http\Resources\BienCollection;
use Illuminate\Http\Request;

class BienController extends Controller
{
    /**
     * 📄 Liste des biens (avec filtres et pagination)
     */
    public function index(Request $request)
    {
        $query = Bien::with('user');

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->transaction_type) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $biens = $query->latest()->paginate(15);

        return new BienCollection($biens); // Retourne une collection paginée
    }




    public function getAllBiens(Request $request)
    {
        $query = Bien::with('user');

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->transaction_type) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $biens = $query->latest()->paginate(15);

        return new BienCollection($biens); // Retourne une collection paginée
    }

    /**
     * ➕ Création d’un bien
     */
    public function store(Request $request)
    {
        // Validation de base
        $baseRules = [
            'category'          => 'required|in:immobilier,vehicule,meuble,hotel,hebergement',
            'transaction_type'  => 'required|in:vente,location',
            'title'             => 'required|string|max:255',
            'price'             => 'required|numeric|min:0',
            'city'              => 'required|string|max:255',
            'district'          => 'nullable|string|max:255',
            'images'            => 'nullable|array',
            'images.*'          => 'file|mimes:jpg,jpeg,png,gif|max:5120', // 5MB max
            'attributes'        => 'required|array',
            'description'       => 'nullable|string',
        ];

        $rules = match ($request->category) {
            'immobilier' => array_merge($baseRules, [
                'attributes.surface' => 'required|numeric',
                'attributes.rooms'   => 'required|integer|min:1',
                'attributes.bathrooms' => 'nullable|integer|min:0',
                'attributes.furnished' => 'nullable|boolean',
                'attributes.parking' => 'nullable|boolean',
                'attributes.electricity' => 'nullable|boolean',
                'attributes.water' => 'nullable|boolean',
            ]),

            'vehicule' => array_merge($baseRules, [
                'attributes.brand'   => 'required|string',
                'attributes.model'   => 'required|string',
                'attributes.year'    => 'required|integer',
                'attributes.fuel'    => 'nullable|string',
                'attributes.gearbox' => 'nullable|string',
                'attributes.mileage' => 'nullable|integer',
            ]),

            'meuble' => array_merge($baseRules, [
                'attributes.type'       => 'required|string',
                'attributes.material'   => 'nullable|string',
                'attributes.dimensions' => 'nullable|string',
                'attributes.condition'  => 'required|string',
            ]),

            'hotel' => array_merge($baseRules, [
                'attributes.room_type'       => 'nullable|string',
                'attributes.capacity'        => 'required|integer|min:1',
                'attributes.wifi'            => 'nullable|boolean',
                'attributes.air_conditioning'=> 'nullable|boolean',
                'attributes.bathroom_private'=> 'nullable|boolean',
            ]),

            'hebergement' => array_merge($baseRules, [
                'attributes.bedrooms' => 'nullable|integer|min:0',
                'attributes.capacity' => 'required|integer|min:1',
                'attributes.kitchen'  => 'nullable|boolean',
                'attributes.wifi'     => 'nullable|boolean',
                'attributes.rules'    => 'nullable|string',
            ]),
        };

        $data = $request->validate($rules);

        // ------------------ GESTION DES IMAGES ------------------
        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $extension = $image->extension();
                $filename = hash('sha256', time() . uniqid()) . "." . $extension;

                // Stockage correct
                $image->storeAs('bien_images', $filename, 'public');

                // Sauvegarde du chemin public
                $imagePaths[] = asset('storage/bien_images/' . $filename);
            }
        }

        // ------------------ CRÉATION DU BIEN ------------------
        $bien = Bien::create([
            'user_id'          => $request->user()->id,
            'category'         => $data['category'],
            'transaction_type' => $data['transaction_type'],
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'price'            => $data['price'],
            'city'             => $data['city'],
            'district'         => $data['district'] ?? null,
            'images'           => $imagePaths, // ✅ URLs publiques des images
            'attributes'       => $data['attributes'],
            'status'           => 'disponible',
            'actif'            => true,
        ]);

        return new BienResource($bien);
    }

        /**
     * 📄 Liste des biens de l'utilisateur connecté
     */
    public function userBiens(Request $request)
    {
        // Récupère tous les biens de l'utilisateur authentifié
        $biens = Bien::with('user')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15); // ⚠ get() au lieu de firstOrFail()

        return new BienCollection($biens); // Retourne même une collection vide
    }


            /**
     * 📄 Liste des biens de l'utilisateur connecté
     */
    public function getUserBiens(Request $request, User $user)
    {
        // Récupère tous les biens de l'utilisateur authentifié
        $biens = Bien::with('user')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15); // ⚠ get() au lieu de firstOrFail()

        return new BienCollection($biens); // Retourne même une collection vide
    }


     /**
     * ✏️ Mise à jour (propriétaire uniquement)
     */
    public function update(Request $request, $id)
    {
        $bien = Bien::find($id);
        if (!$bien) {
            return response()->json(['message' => 'Bien introuvable'], 404);
        }
        $user = $request->user();

        // ------------------ Vérification des permissions ------------------
        if ($user->id !== $bien->user_id) {
            // Ni propriétaire, ni admin
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        // ------------------ Cas propriétaire ------------------
        // Validation complète mais sans 'actif'
        $baseRules = [
            'title'         => 'sometimes|required|string|max:255',
            'price'         => 'sometimes|required|numeric|min:0',
            'city'          => 'sometimes|required|string|max:255',
            'district'      => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'attributes'    => 'sometimes|required|array',
            'status'        => 'sometimes|required|string',
            'images'        => 'nullable|array',
            'images.*'      => 'file|mimes:jpg,jpeg,png,gif|max:5120',
            'keep_images'   => 'nullable|array',
            'keep_images.*' => 'string',
        ];

        $data = $request->validate($baseRules);

        // ------------------ Gestion des images ------------------
        $imagePaths = $request->input('keep_images', []);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $extension = $image->extension();
                $filename = hash('sha256', time() . uniqid()) . "." . $extension;
                $image->storeAs('bien_images', $filename, 'public');
                $imagePaths[] = asset('storage/bien_images/' . $filename);
            }
        }

        // ------------------ Gestion des attributes ------------------
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $data['attributes'] = array_map(function ($value) {
                if ($value === "1" || $value === 1 || $value === true) return true;
                if ($value === "0" || $value === 0 || $value === false) return false;
                return $value;
            }, $data['attributes']);
        }

        // ------------------ Mise à jour ------------------
        $bien->update(array_merge(
            $request->only([
                'title',
                'description',
                'price',
                'city',
                'district',
                'attributes',
                'status',
            ]),
            ['images' => $imagePaths]
        ));

        return new BienResource($bien);
    }



    /**
     * 🔍 Détails d’un bien
     */
    public function show(Bien $bien)
    {
        return new BienResource($bien->load('user'));
    }


    /**
     * 🗑️ Suppression
     */
    public function destroy($id)
    {
        // Récupérer le bien par id
        $bien = Bien::find($id);

        if (!$bien) {
            return response()->json([
                'message' => 'Bien introuvable.'
            ], 404);
        }

        // Vérification du propriétaire ou admin
        $user = auth()->user();
        if ($bien->user_id !== $user->id && $user->account_type !== 'admin') {
            return response()->json([
                'message' => 'Accès refusé.'
            ], 403);
        }

        // Supprimer les images du stockage
        if (!empty($bien->images) && is_array($bien->images)) {
            foreach ($bien->images as $imageUrl) {
                $path = str_replace(asset('storage') . '/', '', $imageUrl);
                \Storage::disk('public')->delete($path);
            }
        }

        // Supprimer le bien
        $bien->delete();

        return response()->json([
            'message' => 'Bien supprimé avec succès.'
        ]);
    }

}
