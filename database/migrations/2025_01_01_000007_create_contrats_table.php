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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->foreignId('technicien_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->decimal('prime_standard', 12, 2);
            $table->decimal('prime_totale', 12, 2);
            $table->decimal('frais_gestion', 12, 2);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
}; 