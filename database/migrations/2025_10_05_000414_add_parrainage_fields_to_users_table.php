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
        Schema::table('users', function (Blueprint $table) {
            $table->string('code_parrainage')->nullable()->after('personne_id');
            $table->unsignedBigInteger('commercial_id')->nullable()->after('code_parrainage');
            $table->boolean('compte_cree_par_commercial')->default(false)->after('commercial_id');
            
            $table->foreign('commercial_id')->references('id')->on('users')->onDelete('set null');
            $table->index('code_parrainage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropIndex(['code_parrainage']);
            $table->dropColumn(['code_parrainage', 'commercial_id', 'compte_cree_par_commercial']);
        });
    }
};
