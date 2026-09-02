<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_reponses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->cascadeOnDelete();
            $table->foreignId('questionnaire_question_id')->constrained('questionnaire_questions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('valeur')->nullable();
            $table->timestamps();

            $table->unique(['questionnaire_question_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_reponses');
    }
};
