<?php

use App\Enums\StatutSinistreEnum;
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
        Schema::create('sinistres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assure_id')->constrained('assures')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->enum('statut', StatutSinistreEnum::values())->default(StatutSinistreEnum::EN_COURS->value)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinistres');
    }
}; 
