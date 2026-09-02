<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referentiel', function (Blueprint $table) {
            $table->id();
            $table->enum('module', [
                'Bases', 'Conjugaison', 'Grammaire', 'Prononciation',
                'Methodologie', 'Vocabulaire', 'Au Quotidien',
            ])->index();
            $table->string('code')->unique();
            $table->text('contenu');
            // Niveaux CECRL, liste séparée par des virgules (cf. App\Casts\SetCast).
            // VARCHAR pour rester portable (SQLite en test).
            $table->string('niveaux')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referentiel');
    }
};
