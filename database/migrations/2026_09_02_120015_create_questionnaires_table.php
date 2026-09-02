<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Questionnaires en ligne : satisfaction à chaud / à froid, évaluation des
     * acquis. session_formation_id nul = modèle réutilisable pour toutes les
     * sessions.
     */
    public function up(): void
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['satisfaction_chaud', 'satisfaction_froid', 'evaluation_acquis']);
            $table->foreignId('session_formation_id')->nullable()->constrained('session_formations')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
