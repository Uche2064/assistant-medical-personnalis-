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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id_1')->constrained('users')->onDelete('set null');
            $table->foreignId('user_id_2')->constrained('users')->onDelete('set null');
            $table->text('dernier_message')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id_1', 'user_id_2'], 'conversation_utilisateur_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
