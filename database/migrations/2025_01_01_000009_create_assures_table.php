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
        Schema::create('assures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // NULL pour les bénéficiaires
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null'); // Pour clients physiques
            $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->onDelete('set null'); // Pour employés d'entreprise
            $table->foreignId('assure_principal_id')->nullable()->constrained('assures')->onDelete('set null'); // Pour les bénéficiaires
            $table->foreignId('contrat_id')->nullable()->constrained('contrats')->onDelete('set null');
            
            // ✅ CHAMPS PERSONNELS (pour les bénéficiaires qui n'ont pas de compte)
            $table->string('nom')->nullable(); // Pour les bénéficiaires
            $table->string('prenoms')->nullable(); // Pour les bénéficiaires
            $table->date('date_naissance')->nullable(); // Pour les bénéficiaires
            $table->string('sexe')->nullable(); // Pour les bénéficiaires
            
            $table->string('lien_parente')->nullable(); // Sera casté vers LienParenteEnum
            $table->boolean('est_principal')->default(true);
            $table->string('statut')->default('actif'); // Sera casté vers StatutAssureEnum
            $table->date('date_debut_contrat')->nullable();
            $table->date('date_fin_contrat')->nullable();
            $table->string('photo_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assures');
    }
}; 