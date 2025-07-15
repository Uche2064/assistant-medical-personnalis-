<?php

use App\Enums\SexeEnum;
use App\Enums\TypeDemandeurEnum;
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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained("users")->onDelete('set null');
            $table->enum('type_prospect', TypeDemandeurEnum::values());

            // Physique
            $table->string('nom')->nullable();
            $table->string('prenoms')->nullable();
            $table->date('date_naissance')->nullable();
            $table->enum('sexe', SexeEnum::values())->nullable();
            $table->string('profession')->nullable();

            // Moral
            $table->string('raison_sociale')->nullable();

            // Commun
            $table->string('contact');
            $table->string('adresse');
            $table->string('email')->unique();
            $table->integer('nombre_de_beneficiaires')->nullable()->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
