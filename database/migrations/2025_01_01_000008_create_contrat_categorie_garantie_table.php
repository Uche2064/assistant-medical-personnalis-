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
            $table->foreignId('contrat_id')->constrained('contrats')->onDelete('cascade');
            $table->foreignId('categorie_garantie_id')->constrained('categories_garanties')->onDelete('cascade');
            $table->decimal('couverture', 5, 2);
            $table->timestamps();
            
            $table->primary(['contrat_id', 'categorie_garantie_id']);
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