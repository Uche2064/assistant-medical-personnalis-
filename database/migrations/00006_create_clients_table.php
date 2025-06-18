<?php

use App\Enums\StatutValidationEnum;
use App\Enums\TypeClientEnum;
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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('gestionnaire_id')->constrained('gestionnaires')->onDelete('cascade');
            $table->string('profession')->unique();
            $table->enum('type_client', TypeClientEnum::values())->nullable();
            $table->enum('statut_validation', StatutValidationEnum::values());
            $table->decimal('prime', 10, 2)->nullable();
            $table->date('date_paiement_prime')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
