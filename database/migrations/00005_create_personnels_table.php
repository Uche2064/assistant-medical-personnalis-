<?php

use App\Enums\TypePersonnelEnum;
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
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('gestionnaire_id')->nullable()->constrained('gestionnaires')->onDelete('set null');
            $table->foreignId('compagnie_id')->constrained('compagnies')->onDelete('cascade');
            $table->enum('type_personnel', TypePersonnelEnum::values());
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
