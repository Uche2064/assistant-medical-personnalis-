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
        Schema::create('assures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // NULL pour les bénéficiaires
            $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->onDelete('set null'); // Pour employés d'entreprise
            $table->foreignId('assure_principal_id')->nullable()->constrained('assures')->onDelete('set null'); // Pour les bénéficiaires
            $table->foreignId('contrat_id')->nullable()->constrained('contrats')->onDelete('set null');
            $table->foreignId('demande_adhesion_id')->nullable()->constrained('demandes_adhesions')->onDelete('set null');
            
            $table->string('nom')->nullable();
            $table->string('prenoms')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('sexe')->nullable();
            $table->string('profession')->nullable();
            $table->string('contact')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('lien_parente')->nullable();
            $table->foreignId('commercial_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->boolean('est_principal')->default(false);
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assures');
    }
}; 