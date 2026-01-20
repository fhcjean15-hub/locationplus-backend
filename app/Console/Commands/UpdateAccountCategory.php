<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountCategory;

class UpdateAccountCategory extends Command
{
    protected $signature = 'app:update-account-category';
    protected $description = 'Modifier une catégorie de compte existante';

    public function handle()
    {
        $this->info('✏️ Modification d’une catégorie de compte');

        // ---------------------------
        // Lister les catégories
        // ---------------------------
        $categories = AccountCategory::all();

        if ($categories->isEmpty()) {
            $this->error('❌ Aucune catégorie trouvée.');
            return Command::FAILURE;
        }

        // ---------------------------
        // Choix
        // ---------------------------
        $choices = $categories->map(fn($c) => "{$c->id} - {$c->name} ({$c->price} FCFA)")->toArray();

        $choice = $this->choice('Quelle catégorie veux-tu modifier ?', $choices);

        preg_match('/^(\d+)/', $choice, $matches);
        $id = $matches[1];

        $category = AccountCategory::findOrFail($id);

        // ---------------------------
        // Questions (avec valeurs actuelles)
        // ---------------------------
        $name = $this->ask('Nom', $category->name);
        $kind = $this->ask('Type / kind', $category->kind);
        $maxAnnonces = (int) $this->ask('Nombre max d’annonces', $category->max_annonces);
        $price = (float) $this->ask('Prix de l’abonnement', $category->price);
        $description = $this->ask('Description', $category->description);

        // ---------------------------
        // Sauvegarde
        // ---------------------------
        $category->update([
            'name' => $name,
            'kind' => $kind,
            'max_annonces' => $maxAnnonces,
            'price' => $price,
            'description' => $description ?: null,
        ]);

        // ---------------------------
        // Succès
        // ---------------------------
        $this->info('✅ Catégorie mise à jour avec succès !');

        return Command::SUCCESS;
    }
}
