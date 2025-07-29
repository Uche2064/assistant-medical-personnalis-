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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->string('numero_police')->unique();
            $table->string('type_contrat'); // Sera casté vers TypeContratEnum
            $table->foreignId('technicien_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->decimal('prime_standard', 12, 2);
            $table->decimal('frais_gestion', 5, 2)->default(20.00); // 20% par défaut
            $table->decimal('commission_commercial', 5, 2)->default(3.00); // 3% par défaut
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('statut')->default('propose'); // Sera casté vers StatutContratEnum
            $table->boolean('est_actif')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
}; 