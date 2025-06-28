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
        Schema::create('reseau_prestataire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')
                ->nullable()
                ->constrained('prestataires')
                ->onDelete('set null');
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->onDelete('set null');
            $table->timestamp('date_creation');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseau_prestataire');
    }
};
