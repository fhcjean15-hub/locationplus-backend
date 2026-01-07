<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('biens', function (Blueprint $table) {
            $table->id();

            // 🔗 Propriétaire
            $table->uuid('user_id');

            // 🏷️ Type de bien
            $table->enum('category', [
                'immobilier',
                'vehicule',
                'meuble',
                'hotel',
                'hebergement'
            ]);

            // 🔄 Vente / Location
            $table->enum('transaction_type', ['vente', 'location']);

            // 📄 Infos principales
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);

            // 📍 Localisation
            $table->string('city');
            $table->string('district')->nullable();

            // 🖼️ Images
            $table->json('images')->nullable();

            // 🧩 Attributs spécifiques
            $table->json('attributes');

            // 📊 Statut
            $table->enum('status', ['disponible', 'indisponible', 'archive'])
                  ->default('disponible');

            
            $table->boolean('actif')->default(true);


            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biens');
    }
};
