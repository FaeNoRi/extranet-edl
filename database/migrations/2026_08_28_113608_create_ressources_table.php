<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ressources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nom');
            $table->enum('type_fichier', ['audio', 'video', 'pdf', 'image', 'autre']);
            $table->text('chemin_fichier');
            $table->string('nom_fichier_original');
            $table->integer('nb_telechargement')->default(0);
            $table->foreignId('uploader_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ressources');
    }
};
