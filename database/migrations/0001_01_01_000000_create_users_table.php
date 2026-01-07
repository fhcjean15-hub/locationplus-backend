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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name', 150)->nullable();
            $table->string('company_name', 200)->nullable();
            $table->string('email', 180)->unique();
            $table->string('phone', 30)->unique()->nullable(); // représente déjà numéro

            // 🔥 Ajouts demandés
            $table->string('ifu', 50)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('ville', 120)->nullable();

            $table->text('password');
            $table->enum('account_type', ['particulier','entreprise','admin'])->default('particulier');
            $table->unsignedBigInteger('account_category_id')->nullable();
            $table->json('documents_urls')->nullable();
            $table->boolean('verified_documents')->default(false);
            $table->boolean('activated')->default(false);
            $table->enum('payment_status', ['none','pending','paid'])->default('none');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
