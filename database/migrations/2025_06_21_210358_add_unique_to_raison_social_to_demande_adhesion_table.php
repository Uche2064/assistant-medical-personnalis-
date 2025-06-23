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
        Schema::table('demandes_adhesions', function (Blueprint $table) {
            $table->unique('raison_sociale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_adhesions', function (Blueprint $table) {
            $table->dropUnique('raison_sociale');
        });
    }
};
