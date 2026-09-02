<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jours de planning d'une session (surtout OP) : dérivés de l'export GESCOF,
     * l'administrateur pouvant « décocher » les jours sans séance (fériés,
     * vacances). Le planning affiché au stagiaire ne montre que les jours actifs.
     */
    public function up(): void
    {
        Schema::create('session_jours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_formation_id')->constrained('session_formations')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('actif')->default(true);
            $table->string('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['session_formation_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_jours');
    }
};
