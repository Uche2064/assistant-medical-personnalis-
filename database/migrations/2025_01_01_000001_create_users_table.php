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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('contact')->nullable();
            $table->string('password');
            $table->string('adresse')->nullable();
            $table->string('photo_url')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->decimal('solde')->default(0);
            $table->timestamp('email_verifier_a')->nullable();
            $table->boolean('mot_de_passe_a_changer')->default(true);
            $table->foreignId('personne_id')->constrained('personnes')->onDelete('cascade');
            $table->integer('failed_attempts')->default(0); // Tentatives échouées consécutives
            $table->dateTime('lock_until')->nullable();     // Jusqu’à quand l’utilisateur est bloqué
            $table->boolean('permanently_blocked')->default(false); // Compte bloqué définitivement
            $table->integer('phase')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });

        // Tables système Laravel
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
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
