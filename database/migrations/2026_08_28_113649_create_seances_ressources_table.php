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
        Schema::create('seances_ressources', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('seance_id');
            $table->unsignedInteger('ressource_id');

            $table->foreign('seance_id')->references('id')->on('seances');
            $table->foreign('ressource_id')->references('id')->on('ressources');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seances_ressources');
    }
};
