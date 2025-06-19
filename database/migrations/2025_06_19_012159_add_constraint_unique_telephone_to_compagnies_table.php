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
        Schema::table('compagnies', function (Blueprint $table) {
            $table->unique('telephone');
            $table->unique('email');
            $table->unique('site_web');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compagnies', function (Blueprint $table) {
            $table->dropUnique('telephone');
            $table->dropUnique('site_web');
            $table->dropUnique('email');
        });
    }
};
