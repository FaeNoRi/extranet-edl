<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Documents administratifs. Deux catégories affichées côté stagiaire :
     *  - presentation_structure : communs à tous (locaux, registre
     *    d'accessibilité, catalogue, liste du matériel) ;
     *  - mes_documents : convention/contrat, guide d'animation, livret
     *    d'accueil, questionnaires d'évaluation — personnalisables par session.
     *
     * session_formation_id nul = document général de la structure.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('categorie', ['presentation_structure', 'mes_documents']);
            $table->string('type_document')->nullable()->comment('Convention, Livret d\'accueil, ...');
            $table->foreignId('session_formation_id')->nullable()->constrained('session_formations')->cascadeOnDelete();
            $table->string('chemin_fichier');
            $table->string('nom_fichier_original');
            $table->unsignedBigInteger('taille')->default(0);
            $table->foreignId('uploader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
