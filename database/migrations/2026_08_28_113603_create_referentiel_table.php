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
            $table->set('niveaux', ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])->nullable();
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
