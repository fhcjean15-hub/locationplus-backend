<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('bien_id');
            $table->uuid('user_id')->nullable();
            $table->uuid('owner_id');

            // 🔑 Tracking invité
            $table->string('tracking_token')->nullable()->index();

            // Infos client
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone');

            // Infos bien
            $table->string('category');
            $table->string('transaction_type');
            $table->string('reservation_type');
            $table->decimal('price', 12, 2);

            // Dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('visit_date')->nullable();

            // Détails
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])
                ->default('pending');

            $table->timestamps();

            $table->index(['bien_id', 'owner_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
