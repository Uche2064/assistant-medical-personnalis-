<?php

use App\Enums\StatutClientPrestataireEnum;
use App\Enums\TypePrestataireEnum;
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
            $table->foreignId('client_contrat_id')->constrained('client_contrats')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->enum('type_prestataire', TypePrestataireEnum::values());
            $table->enum('statut', StatutClientPrestataireEnum::values())->default(StatutClientPrestataireEnum::ACTIF);
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['client_contrat_id', 'statut']);
            $table->index(['prestataire_id', 'type_prestataire']);

            $table->unique(['prestataire_id', 'client_contrat_id'], 'client_prestataire_unique');

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
