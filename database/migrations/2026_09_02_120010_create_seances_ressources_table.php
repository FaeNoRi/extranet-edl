<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ressources rattachées à une séance. transmis = true : visible dans
     * l'espace du stagiaire ; false : document de travail du formateur.
     */
    public function up(): void
    {
        Schema::create('seances_ressources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seance_id')->constrained('seances')->cascadeOnDelete();
            $table->foreignId('ressource_id')->constrained('ressources')->cascadeOnDelete();
            $table->boolean('transmis')->default(false);

            $table->unique(['seance_id', 'ressource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seances_ressources');
    }
};
