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
        Schema::create('client_contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('contrat_id')->constrained('contrats')->onDelete('cascade');
            $table->enum('type_client', ['physique', 'entreprise']);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['ACTIF', 'INACTIF', 'EXPIRE'])->default('ACTIF');
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['client_id', 'statut']);
            $table->index(['contrat_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_contrats');
    }
};
