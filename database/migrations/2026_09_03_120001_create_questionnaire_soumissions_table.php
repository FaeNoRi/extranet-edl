<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marque la soumission d'un questionnaire par un utilisateur (un seul envoi
     * possible). Les réponses détaillées sont dans questionnaire_reponses.
     */
    public function up(): void
    {
        Schema::create('questionnaire_soumissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('soumis_at');
            $table->timestamps();

            $table->unique(['questionnaire_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_soumissions');
    }
};
