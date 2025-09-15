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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('type_de_donnee');
            $table->json('options')->nullable();
            $table->string('destinataire');
            $table->boolean('est_obligatoire')->default(false);
            $table->boolean('est_active')->default(true);
            $table->foreignId('cree_par_id')->constrained('personnels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
}; 
