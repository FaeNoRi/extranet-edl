<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une séance = une « fiche pédagogique ». Le dossier de la séance (nommé
     * d'après sa date) regroupe la fiche PDF, les ressources et les fiches du
     * référentiel des modules cochés.
     */
    public function up(): void
    {
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_formation_id')->constrained('session_formations')->cascadeOnDelete();
            $table->foreignId('formateur_id')->nullable()->constrained('users')->nullOnDelete();
            // Renseigné pour les fiches FPC (suivi individuel) ; nul pour une
            // séance de groupe OP.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('date');
            $table->string('langue')->nullable();

            // Objectifs cochés dans le formulaire (libellés), en JSON.
            $table->json('objectifs')->nullable();
            $table->text('contenu')->nullable();

            // Types d'outils/supports cochés (Livres, Magazines, Vidéos, ...).
            $table->json('outils')->nullable();
            $table->text('sources')->nullable();

            $table->text('analyse_seance')->nullable();

            // Fiche pédagogique PDF générée à l'enregistrement.
            $table->string('fiche_pdf_path')->nullable();

            $table->timestamps();

            $table->index(['session_formation_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};
