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
        Schema::create('propositions_contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_adhesion_id')->constrained('demandes_adhesions')->onDelete('cascade');
            $table->foreignId('contrat_id')->constrained('types_contrats')->onDelete('cascade');
            $table->text('commentaires_technicien')->nullable();
            $table->foreignId('technicien_id')->constrained('personnels')->onDelete('cascade');
            $table->string('statut')->default('proposee');
            $table->timestamp('date_proposition')->default(now());
            $table->timestamp('date_acceptation')->nullable();
            $table->timestamp('date_refus')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propositions_contrats');
    }
}; 
