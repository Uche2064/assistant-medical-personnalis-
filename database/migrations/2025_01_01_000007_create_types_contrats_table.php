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
        Schema::create('types_contrats', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->decimal('prime_standard', 12, 2);
            $table->boolean('est_actif')->default(true);
            $table->foreignId('technicien_id')->nullable()->constrained('personnels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_contrats');
    }
}; 
