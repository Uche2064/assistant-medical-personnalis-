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
            $table->string('motif_rejet')->nullable()->after('valider_a');
            $table->json('infos_complementaires')->nullable()->after('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_adhesions', function (Blueprint $table) {
            $table->dropColumn('motif_rejet');
            $table->dropColumn('infos_complementaires');
        });
    }
};