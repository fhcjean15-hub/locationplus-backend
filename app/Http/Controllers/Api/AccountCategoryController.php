<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountCategory;
use App\Http\Resources\AccountCategoryResource;
use App\Http\Resources\AccountCategoryCollection;

class AccountCategoryController extends Controller
{
    /**
     * Liste complète.
     */
    public function index()
    {
        $categories = AccountCategory::latest()->get();
        return new AccountCategoryCollection($categories);
    }

    /**
     * Liste des catégories AGENT.
     */
    public function agents()
    {
        $categories = AccountCategory::where('kind', 'agent')->get();
        return new AccountCategoryCollection($categories);
    }

    /**
     * Liste des catégories AGENCE.
     */
    public function agences()
    {
        $categories = AccountCategory::where('kind', 'agence')->get();
        return new AccountCategoryCollection($categories);
    }

    /**
     * Store.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150|unique:account_categories,name',
            'kind'          => 'required|in:agent,agence', // 🔥 NEW
            'max_annonces'  => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
        ]);

        $category = AccountCategory::create($validated);

        return new AccountCategoryResource($category);
    }

    /**
     * Show.
     */
    public function show(string $id)
    {
        $category = AccountCategory::findOrFail($id);
        return new AccountCategoryResource($category);
    }

    /**
     * Update.
     */
    public function update(Request $request, string $id)
    {
        $category = AccountCategory::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'sometimes|string|max:150|unique:account_categories,name,' . $category->id,
            'kind'          => 'sometimes|in:agent,agence', // 🔥 NEW
            'max_annonces'  => 'sometimes|integer|min:0',
            'description'   => 'sometimes|nullable|string',
        ]);

        $category->update($validated);

        return new AccountCategoryResource($category);
    }

    /**
     * Delete.
     */
    public function destroy(string $id)
    {
        $category = AccountCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès.'
        ]);
    }
}
