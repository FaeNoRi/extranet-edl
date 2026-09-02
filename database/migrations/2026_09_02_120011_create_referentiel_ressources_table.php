<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referentiel_ressources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referentiel_id')->constrained('referentiel')->cascadeOnDelete();
            $table->foreignId('ressource_id')->constrained('ressources')->cascadeOnDelete();

            $table->unique(['referentiel_id', 'ressource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referentiel_ressources');
    }
};
