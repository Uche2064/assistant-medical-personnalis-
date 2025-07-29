<?php

use App\Enums\StatutClientEnum;
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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenoms');
            $table->string('sexe');
            $table->date('date_naissance');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('commercial_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->string('type_client');
            $table->string('profession')->nullable();
            $table->string('code_parainage')->nullable();
            $table->string('statut')->default(StatutClientEnum::PROSPECT);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
}; 