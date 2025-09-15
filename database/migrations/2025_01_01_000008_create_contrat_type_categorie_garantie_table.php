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
        Schema::create('contrat_categorie_garantie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_contrat_id')->constrained('types_contrats')->onDelete('cascade');
            $table->foreignId('categorie_garantie_id')->constrained('categories_garanties')->onDelete('cascade');
            $table->decimal('couverture', 5, 2);
            $table->decimal('frais_gestion', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrat_categorie_garantie');
    }
}; 
