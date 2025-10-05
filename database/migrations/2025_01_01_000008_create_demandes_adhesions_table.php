<?php

use App\Enums\StatutDemandeAdhesionEnum;
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
            $table->string('type_demandeur');
            $table->enum('statut', StatutDemandeAdhesionEnum::values())->default(StatutDemandeAdhesionEnum::EN_ATTENTE);
            $table->text('motif_rejet')->nullable();
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('cascade');
            $table->timestamp('valider_a')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
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
