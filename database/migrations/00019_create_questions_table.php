<?php

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->enum('type_donnees', TypeDonneeEnum::values());
            $table->json('options')->nullable();
            $table->enum('destinataire', TypeDemandeurEnum::values());
            $table->boolean('obligatoire')->default(true);
            $table->boolean('est_actif')->default(true);
            $table->foreignId('cree_par_id')->nullable()->constrained('personnels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
