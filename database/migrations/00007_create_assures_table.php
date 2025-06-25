<?php

use App\Enums\LienEnum;
use App\Enums\LienParenteEnum;
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
        Schema::create('assures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('set null');
            $table->foreignId('client_id')->constrained('clients')->onDelete('set null');
            $table->foreignId('assure_parent_id')->nullable()->constrained('assures')->onDelete('set null');
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
        Schema::dropIfExists('assures');
    }
};
