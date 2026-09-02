<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ressources', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('type_fichier', ['audio', 'video', 'pdf', 'image', 'autre']);
            $table->string('chemin_fichier');
            $table->string('nom_fichier_original');
            $table->unsignedBigInteger('taille')->default(0)->comment('Taille du fichier en octets');
            $table->unsignedInteger('nb_telechargement')->default(0);
            $table->foreignId('uploader_id')->constrained('users');
            // Ressources FPC déposées au niveau d'une session (facultatif).
            $table->foreignId('session_formation_id')->nullable()->constrained('session_formations')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ressources');
    }
};
