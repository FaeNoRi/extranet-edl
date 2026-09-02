<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Suivi des fiches du référentiel mises à disposition d'un stagiaire et de
     * leur consultation. L'accès « normal » découle des séances réalisées ;
     * cette table permet un octroi explicite et le suivi de lecture.
     */
    public function up(): void
    {
        Schema::create('user_referentiel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referentiel_id')->constrained('referentiel')->cascadeOnDelete();
            $table->timestamp('consulte_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'referentiel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_referentiel');
    }
};
