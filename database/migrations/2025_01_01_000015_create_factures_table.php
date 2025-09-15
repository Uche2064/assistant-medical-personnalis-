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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->text('diagnostic');
            $table->string('numero_facture')->unique();
            $table->foreignId('sinistre_id')->constrained('sinistres')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade');
            $table->decimal('montant_facture', 12, 2);
            $table->decimal('ticket_moderateur', 12, 2);
            $table->string('statut')->default('en_attente');
            $table->text('motif_rejet')->nullable();
            
            // Validation par technicien
            $table->foreignId('technicien_id')->nullable()->constrained('personnels')->onDelete('cascade');
            $table->timestamp('valide_par_technicien_a')->nullable();
            
            // Validation par médecin
            $table->foreignId('medecin_id')->nullable()->constrained('personnels')->onDelete('cascade');
            $table->timestamp('valide_par_medecin_a')->nullable();
            
            // Autorisation par comptable
            $table->foreignId('comptable_id')->nullable()->constrained('personnels')->onDelete('cascade');
            $table->timestamp('autorise_par_comptable_a')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
}; 
