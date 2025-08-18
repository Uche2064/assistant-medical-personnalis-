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
        Schema::table('factures', function (Blueprint $table) {
            // Champs de rejet par technicien
            $table->text('motif_rejet_technicien')->nullable();
            $table->timestamp('rejet_par_technicien_a')->nullable();
            
            // Champs de rejet par médecin
            $table->text('motif_rejet_medecin')->nullable();
            $table->timestamp('rejet_par_medecin_a')->nullable();
            
            // Champs de rejet par comptable
            $table->text('motif_rejet_comptable')->nullable();
            $table->timestamp('rejet_par_comptable_a')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn([
                'motif_rejet_technicien',
                'rejet_par_technicien_a',
                'motif_rejet_medecin',
                'rejet_par_medecin_a',
                'motif_rejet_comptable',
                'rejet_par_comptable_a'
            ]);
        });
    }
};
