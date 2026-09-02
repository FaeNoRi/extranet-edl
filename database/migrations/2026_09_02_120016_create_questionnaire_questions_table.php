<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->cascadeOnDelete();
            $table->string('libelle');
            $table->enum('type', ['texte', 'choix_unique', 'choix_multiple', 'echelle']);
            $table->json('options')->nullable();
            $table->boolean('obligatoire')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_questions');
    }
};
