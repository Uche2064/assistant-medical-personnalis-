<?php

use App\Enums\SexeEnum;
use App\Enums\StatutValidation;
use App\Enums\StatutValidationEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypePrestataireEnum;
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
            $table->enum('type_demandeur', TypeDemandeurEnum::values());
            $table->enum('statut', StatutValidationEnum::values())->default('en_attente');
            $table->text('motif_rejet')->nullable();
            $table->foreignId('prospect_id')->constrained('prospects')->onDelete('cascade');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels'); // technicien ou medecin
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
