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
        Schema::create('demandes_adhesions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type_demandeur'); // Sera casté vers TypeDemandeurEnum
            $table->string('statut')->default('en_attente'); // Sera casté vers StatutDemandeAdhesionEnum
            $table->text('motif_rejet')->nullable();
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->string('code_parainage')->nullable();
            $table->timestamp('valider_a')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes_adhesions');
    }
}; 