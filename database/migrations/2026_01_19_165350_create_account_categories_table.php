<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('kind', 20); // 🔥 NEW : agent | agence
            $table->integer('max_annonces')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_categories');
    }
};
//  Quelle catégorie veux-tu modifier ?:
//   [0] 1 - Agent de vente de meubles (0.00 FCFA)
//   [1] 2 - Agence de vente de meubles (10000.00 FCFA)
//   [2] 3 - Agent de ventes et locations véhicules (7000.00 FCFA)
//   [3] 4 - Agence de ventes et locations véhicules (5000.00 FCFA)
//   [4] 5 - Agent immobilier (0.00 FCFA)
//   [5] 6 - Agence immobilière (0.00 FCFA)
//   [6] 7 - Agent d'hôtellerie et hébergement (0.00 FCFA)
//   [7] 8 - Agence d'hôtellerie et hébergement (0.00 FCFA)