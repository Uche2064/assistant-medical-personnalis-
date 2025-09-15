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
        Schema::create('categories_garanties', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->text('description')->nullable();
            $table->boolean('est_active')->default(true);
            $table->foreignId('medecin_controleur_id')->nullable()->constrained('personnels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories_garanties');
    }
}; 
