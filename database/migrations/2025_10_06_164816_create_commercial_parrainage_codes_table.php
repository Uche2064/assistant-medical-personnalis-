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
        Schema::create('commercial_parrainage_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commercial_id');
            $table->string('code_parrainage');
            $table->timestamp('date_debut');
            $table->timestamp('date_expiration');
            $table->boolean('est_actif')->default(true);
            $table->boolean('est_renouvele')->default(false);
            $table->timestamps();

            $table->foreign('commercial_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['commercial_id', 'code_parrainage']);
            $table->index(['commercial_id', 'est_actif']);
            $table->index('date_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commercial_parrainage_codes');
    }
};