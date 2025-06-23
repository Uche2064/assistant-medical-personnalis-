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
        Schema::create('reponses_questionnaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_adhesion_id')->constrained('questions')->onDelete('cascade');
            $table->json('reponses');
            $table->boolean('est_validee')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reponses_questionnaire');
    }
};
