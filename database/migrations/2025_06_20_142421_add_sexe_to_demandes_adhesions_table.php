<?php

use App\Enums\SexeEnum;
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
            $table->enum('sexe', SexeEnum::values())->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_adhesions', function (Blueprint $table) {
            $table->dropColumn('sexe');
        });
    }
};
