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
            $table->text('libelle');
            $table->string('type_donnee'); // Sera casté vers TypeDonneeEnum
            $table->json('options')->nullable(); // Pour les questions à choix multiples
            $table->string('destinataire'); // Sera casté vers TypeDemandeurEnum
            $table->boolean('obligatoire')->default(false);
            $table->boolean('est_actif')->default(true);
            $table->foreignId('cree_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
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