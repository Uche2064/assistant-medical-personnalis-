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
        Schema::create('client_prestataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_contrat_id')->constrained('clients_contrats')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->string('type_prestataire'); // pharmacie, centre_soins, optique, etc.
            $table->string('statut')->default('actif'); // actif, inactif
            $table->timestamps();

            // Index pour éviter les doublons
            $table->unique(['client_contrat_id', 'prestataire_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_prestataires');
    }
};
