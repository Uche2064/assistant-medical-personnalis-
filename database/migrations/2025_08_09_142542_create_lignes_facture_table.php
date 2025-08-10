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
        Schema::create('lignes_facture', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->foreignId('garantie_id')->constrained('garanties')->onDelete('cascade');
            $table->string('libelle_acte');
            $table->decimal('prix_unitaire', 10, 2);
            $table->integer('quantite')->default(1);
            $table->decimal('prix_total', 10, 2);
            $table->decimal('taux_couverture', 5, 2); // Pourcentage de couverture
            $table->decimal('montant_couvert', 10, 2); // Montant pris en charge par l'assurance
            $table->decimal('ticket_moderateur', 10, 2); // Montant à la charge du patient
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lignes_facture');
    }
};
