<?php

use App\Enums\StatutFactureEnum;
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

            $table->decimal('montant_reclame', 12, 2);
            $table->decimal('montant_a_rembourser', 12, 2);
            $table->text('diagnostic');
            $table->json('photo_justificatifs');
            $table->decimal('ticket_moderateur', 12, 2);
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('set null');

            $table->enum('statut', StatutFactureEnum::values())->default(StatutFactureEnum::EN_ATTENTE);
            $table->foreignId('sinistre_id')->constrained('sinistres')->onDelete('set null');

            $table->boolean('est_valide_par_medecin')->default(false);
            $table->foreignId('medecin_id')->nullable()->constrained('personnels')->onDelete('set null'); // ou personnel_id filtré par type
            $table->timestamp('valide_par_medecin_a')->nullable();

            $table->boolean('est_valide_par_technicien')->default(false);
            $table->foreignId('technicien_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('valide_par_technicien_a')->nullable();

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
