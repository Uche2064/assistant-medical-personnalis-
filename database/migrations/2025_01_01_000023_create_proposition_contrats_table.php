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
        Schema::create('proposition_contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_adhesion_id')->constrained('demandes_adhesions')->onDelete('cascade');
            $table->foreignId('contrat_id')->constrained('contrats')->onDelete('cascade');
            $table->text('commentaires_technicien')->nullable();
            $table->foreignId('technicien_id')->constrained('personnels')->onDelete('cascade');
            $table->string('statut')->default('proposee'); // Sera casté vers StatutPropositionContratEnum
            $table->timestamp('date_proposition')->default(now());
            $table->timestamp('date_acceptation')->nullable();
            $table->timestamp('date_refus')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Table pivot pour les garanties de la proposition
        Schema::create('proposition_contrat_garantie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposition_contrat_id')->constrained('proposition_contrats')->onDelete('cascade');
            $table->foreignId('garantie_id')->constrained('garanties')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposition_contrat_garantie');
        Schema::dropIfExists('proposition_contrats');
    }
}; 