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
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('demande_adhesion_id')->nullable()->constrained('demandes_adhesions')->onDelete('set null');
            
            // ✅ AJOUTER : Relation polymorphique vers la personne qui a répondu
            $table->string('personne_type'); // 'App\Models\User' ou 'App\Models\Assure'
            $table->unsignedBigInteger('personne_id'); // ID de la personne
            
            $table->text('reponse_text')->nullable();
            $table->boolean('est_vue')->default(false);
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