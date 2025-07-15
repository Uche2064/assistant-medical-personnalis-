<?php

use App\Enums\LienEnum;
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
          Schema::create('beneficiaires_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_temp_id')->constrained('employes_temp')->onDelete('cascade');
            $table->string('nom');
            $table->string('prenoms');
            $table->date('date_naissance');
            $table->enum('sexe', SexeEnum::values());
            $table->string('email')->unique()->nullable();
            $table->string('contact')->unique()->nullable();
            $table->enum('lien_parente', LienEnum::values());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaires_temp');
    }
};
