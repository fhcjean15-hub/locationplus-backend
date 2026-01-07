<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', ['paiement','demande','signalement','admin_action','compte_validé','compte_rejeté','profile_update'])
                ->default('demande')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', ['paiement','demande','signalement','admin_action','compte_validé','compte_rejeté'])
                ->default('demande')
                ->change();
        });
    }

};
