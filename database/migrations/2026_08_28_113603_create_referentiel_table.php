<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referentiel', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('module', [
                'Bases', 'Conjugaison', 'Grammaire', 'Prononciation',
                'Methodologie', 'Vocabulaire', 'Au Quotidien',
            ])->index();
            $table->string('code');
            $table->text('contenu');
            // Liste de niveaux CECRL séparés par des virgules (cf. App\Casts\SetCast).
            // Stockée en VARCHAR pour rester portable (SQLite en test, MySQL en prod).
            $table->string('niveaux')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referentiel');
    }
};
