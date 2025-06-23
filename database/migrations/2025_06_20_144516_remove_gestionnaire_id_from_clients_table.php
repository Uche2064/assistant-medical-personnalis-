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
        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('gestionnaire_id', 'technicien_id');
            $table->dropUnique('clients_profession_unique');
            $table->foreign('technicien_id')->nullable()->references('id')->on('personnels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('technicien_id', 'gestionnaire_id');
            $table->unique('profession');
            $table->foreign('gestionnaire_id')->nullable()->references('id')->on('personnels')->onDelete('cascade');
        });
    }
};
