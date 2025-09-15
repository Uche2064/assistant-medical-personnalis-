<?php

use App\Enums\LienParenteEnum;
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
        Schema::create('assures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // NULL pour les bénéficiaires
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Pour employés d'entreprise
            $table->enum('lien_parente', LienParenteEnum::values())->nullable();
            $table->boolean('est_principal')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assures');
    }
}; 
