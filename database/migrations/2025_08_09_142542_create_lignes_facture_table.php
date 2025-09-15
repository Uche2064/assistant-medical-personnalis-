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
            $table->string('libelle');
            $table->decimal('prix_unitaire', 10, 2);
            $table->integer('quantite')->default(1);
            $table->timestamps();
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
