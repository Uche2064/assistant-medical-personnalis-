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
        Schema::create('compagnies', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('adresse')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('site_web')->nullable();
            $table->string('logo')->nullable();
            $table->string('description')->nullable();
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
        Schema::dropIfExists('compagnie');
    }
};
