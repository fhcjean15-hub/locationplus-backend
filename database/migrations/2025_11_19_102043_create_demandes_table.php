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
        Schema::create('demandes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('annonce_id');
            $table->enum('type', ['achat','location','reservation']);
            $table->string('requester_name', 120);
            $table->string('requester_email', 150)->nullable();
            $table->string('requester_phone', 30)->nullable();
            $table->text('message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('annonce_id')->references('id')->on('annonces')->onDelete('cascade');
            $table->index(['annonce_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
