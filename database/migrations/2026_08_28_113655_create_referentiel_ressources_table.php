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
        Schema::create('referentiel_ressources', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('referentiel_id');
            $table->unsignedInteger('ressources_id');

            $table->foreign('referentiel_id')->references('id')->on('referentiel');
            $table->foreign('ressources_id')->references('id')->on('ressources');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referentiel_ressources');
    }
};
