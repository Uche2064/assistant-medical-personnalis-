<?php

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
            $table->string('nom')->nullable();
            $table->string('raison_sociale')->nullable();
            $table->string('prenoms')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('contact')->unique()->nullable();
            $table->enum('type_demande', TypeDemandeurEnum::values());
            $table->enum('statut', StatutValidationEnum::values())->default(StatutValidationEnum::EN_ATTENTE);
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels'); // médecin contrôleur
            $table->foreignId('fait_par')->nullable()->constrained('personnels'); // dans le cas d'une demande faite par un personnel pour le compte d'un client physique(commercial)
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
