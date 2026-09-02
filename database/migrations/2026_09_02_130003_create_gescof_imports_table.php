<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal des imports GESCOF (exigence CDC : import bimensuel, historique).
     * Conserve le rapport complet de chaque exécution, simulation comprise.
     */
    public function up(): void
    {
        Schema::create('gescof_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('fichier_nom');
            $table->boolean('applique')->default(false);
            $table->unsignedInteger('lignes_lues')->default(0);
            $table->unsignedInteger('lignes_ignorees')->default(0);
            $table->unsignedInteger('comptes_crees')->default(0);
            $table->unsignedInteger('comptes_reactives')->default(0);
            $table->unsignedInteger('comptes_disparus')->default(0);
            $table->unsignedInteger('sessions_creees')->default(0);
            $table->unsignedInteger('sessions_maj')->default(0);
            $table->json('anomalies')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gescof_imports');
    }
};
