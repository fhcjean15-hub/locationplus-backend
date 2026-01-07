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
        Schema::create('signalements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('annonce_id');
            $table->text('motif');
            $table->text('commentaire')->nullable();
            $table->uuid('reporter_user_id')->nullable();
            $table->boolean('processed_by_admin')->default(false);
            $table->enum('action_taken', ['none','disabled','deleted','rejected'])->default('none');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('annonce_id')->references('id')->on('annonces')->onDelete('cascade');
            $table->foreign('reporter_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['annonce_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signalements');
    }
};
