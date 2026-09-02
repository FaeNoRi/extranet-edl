<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modules du référentiel cochés pour une séance. Déclenche l'ajout
     * automatique des fiches du référentiel dans le dossier de la séance.
     */
    public function up(): void
    {
        Schema::create('seances_referentiel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seance_id')->constrained('seances')->cascadeOnDelete();
            $table->foreignId('referentiel_id')->constrained('referentiel')->cascadeOnDelete();

            $table->unique(['seance_id', 'referentiel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seances_referentiel');
    }
};
