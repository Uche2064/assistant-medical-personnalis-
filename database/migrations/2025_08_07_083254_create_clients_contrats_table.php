<?php

use App\Enums\StatutContratEnum;
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
        Schema::create('clients_contrats', function (Blueprint $table) {
            $table->id();
            $table->string('numero_police')->unique()->nullable();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('type_contrat_id')->constrained('types_contrats')->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', StatutContratEnum::values())->default(StatutContratEnum::ACTIF);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_contrats');
    }
};
