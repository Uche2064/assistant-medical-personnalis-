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
            $table->string('numero_facture')->unique();
            $table->foreignId('sinistre_id')->nullable()->constrained('sinistres')->onDelete('set null');
            $table->foreignId('prestataire_id')->nullable()->constrained('prestataires')->onDelete('set null');
            $table->decimal('montant_reclame', 12, 2);
            $table->decimal('montant_a_rembourser', 12, 2);
            $table->text('diagnostic');
            $table->json('photo_justificatifs');
            $table->decimal('ticket_moderateur', 12, 2);
            $table->string('statut')->default('en_attente');
            $table->text('motif_rejet')->nullable();
            
            // Validation par technicien
            $table->boolean('est_valide_par_technicien')->default(false);
            $table->foreignId('technicien_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('valide_par_technicien_a')->nullable();
            
            // Validation par médecin
            $table->boolean('est_valide_par_medecin')->default(false);
            $table->foreignId('medecin_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('valide_par_medecin_a')->nullable();
            
            // Autorisation par comptable
            $table->boolean('est_autorise_par_comptable')->default(false);
            $table->foreignId('comptable_id')->nullable()->constrained('personnels')->onDelete('set null');
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