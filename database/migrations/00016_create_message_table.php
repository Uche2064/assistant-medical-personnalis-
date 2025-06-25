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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('contenu');
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('set null');
            $table->foreignId('expediteur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('envoyer_a')->nullable();
            $table->date('lu_a')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['conversation_id', 'expediteur_id'], 'message_conversation_envoye_recu_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
