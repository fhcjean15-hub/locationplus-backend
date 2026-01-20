<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccountCategory;

class CreateAccountCategory extends Command
{
    protected $signature = 'app:create-account-category';
    protected $description = 'Créer une catégorie de compte (interactive)';

    public function handle()
    {
        $this->info('🧱 Création d’une catégorie de compte');

        // ---------------------------
        // Questions interactives
        // ---------------------------
        $name = $this->ask('Nom de la catégorie (ex: Agent immobilier, Agence, etc.)');
        $kind = $this->ask('Type / kind (ex: agent, agence)');
        $maxAnnonces = (int) $this->ask('Nombre max d’annonces');
        $price = (float) $this->ask('Prix de l’abonnement (ex: 10000)'); // 🔥 NOUVEAU
        $description = $this->ask('Description (optionnel)');

        // ---------------------------
        // Vérification unicité
        // ---------------------------
        if (AccountCategory::where('name', $name)->exists()) {
            $this->error('❌ Une catégorie avec ce nom existe déjà.');
            return Command::FAILURE;
        }

        // ---------------------------
        // Création
        // ---------------------------
        AccountCategory::create([
            'name' => $name,
            'kind' => $kind,
            'max_annonces' => $maxAnnonces,
            'price' => $price, // 🔥 NOUVEAU
            'description' => $description ?: null,
        ]);

        // ---------------------------
        // Succès
        // ---------------------------
        $this->info('✅ Catégorie créée avec succès !');
        $this->line("💰 Prix: {$price} FCFA");
        $this->line("📦 Max annonces: {$maxAnnonces}");

        return Command::SUCCESS;
    }
}
