<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AccountCategory;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';
    protected $description = 'Créer un administrateur (interactive)';

    public function handle()
    {
        $this->info('👤 Création d’un administrateur');

        // ---------------------------
        // Vérifier qu'il existe des catégories
        // ---------------------------
        $categories = AccountCategory::all();

        if ($categories->isEmpty()) {
            $this->error('❌ Aucune catégorie de compte trouvée. Crée-en d’abord une.');
            return Command::FAILURE;
        }

        // ---------------------------
        // Choisir la catégorie
        // ---------------------------
        $choices = $categories->map(fn($c) => "{$c->id} - {$c->name} ({$c->price} FCFA)")->toArray();

        $choice = $this->choice('Choisis la catégorie du compte admin', $choices);

        preg_match('/^(\d+)/', $choice, $matches);
        $categoryId = $matches[1];

        // ---------------------------
        // Infos admin
        // ---------------------------
        $fullName = $this->ask('Nom complet');
        $email = $this->ask('Email');
        $password = $this->secret('Mot de passe');
        $confirm = $this->secret('Confirmer le mot de passe');

        if ($password !== $confirm) {
            $this->error('❌ Les mots de passe ne correspondent pas.');
            return Command::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('❌ Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        // ---------------------------
        // Création
        // ---------------------------
        $user = User::create([
            'id' => Str::uuid(),
            'full_name' => $fullName,
            'email' => $email,
            'password' => Hash::make($password),
            'account_type' => 'admin',
            'account_category_id' => $categoryId,
            'activated' => true,
            'verified_documents' => true,
            'payment_status' => 'paid',
        ]);

        // ---------------------------
        // Succès
        // ---------------------------
        $this->info('✅ Administrateur créé avec succès !');
        $this->line("📧 Email: $email");
        $this->line("🏷️ Catégorie: " . AccountCategory::find($categoryId)->name);

        return Command::SUCCESS;
    }
}
