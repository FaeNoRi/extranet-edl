<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Émargement d'un stagiaire pour une séance (FPC en distanciel).
     */
    public function up(): void
    {
        Schema::create('emargements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seance_id')->constrained('seances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('present')->default(false);
            $table->timestamp('signe_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['seance_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emargements');
    }
};
