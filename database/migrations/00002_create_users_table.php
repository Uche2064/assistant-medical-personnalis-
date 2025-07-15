<?php

use App\Enums\RoleEnum;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('prenoms')->nullable();
            $table->string('raison_sociale')->nullable()->unique();
            $table->string('adresse')->nullable();
            $table->string('email')->unique();
            $table->string('contact')->nullable()->unique();
            $table->date('date_naissance')->nullable();
            $table->enum('sexe', SexeEnum::values())->nullable();
            $table->string('password')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('photo_url')->nullable();
            $table->boolean('mot_de_passe_a_changer')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });



        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
