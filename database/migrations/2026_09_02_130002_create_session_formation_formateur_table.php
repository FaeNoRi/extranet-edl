<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Formateurs d'une session (co-animation possible). `session_formations.formateur_id`
     * reste le formateur référent ; ce pivot liste toute l'équipe.
     */
    public function up(): void
    {
        Schema::create('session_formation_formateur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_formation_id')->constrained('session_formations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('principal')->default(false);
            $table->timestamps();

            $table->unique(['session_formation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_formation_formateur');
    }
};
