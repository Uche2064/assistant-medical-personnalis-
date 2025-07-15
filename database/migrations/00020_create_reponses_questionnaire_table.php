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
            $table->unsignedBigInteger('personne_id');
            $table->string('personne_type'); // morph: assures ou prospects
            $table->foreignId('question_id')->nullable()->constrained('questions')->onDelete('set null');
            $table->string('reponse_text')->nullable();
            $table->boolean('reponse_bool')->nullable();
            $table->decimal('reponse_decimal', 12, 2)->nullable();
            $table->date('reponse_date')->nullable();
            $table->string('reponse_fichier')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
