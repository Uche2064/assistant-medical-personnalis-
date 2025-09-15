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
        Schema::create('garanties', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->decimal('plafond', 12, 2);
            $table->decimal('prix_standard', 12, 2);
            $table->decimal('taux_couverture', 5, 2);
             $table->foreignId('categorie_garantie_id')->constrained('categories_garanties')->onDelete('cascade');
            $table->foreignId('medecin_controleur_id')->constrained('personnels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garanties');
    }
}; 
