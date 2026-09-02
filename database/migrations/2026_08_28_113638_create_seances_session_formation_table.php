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
        Schema::create('seances_session_formation', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('seance_id');
            $table->unsignedInteger('session_formation_id');

            $table->foreign('seance_id')->references('id')->on('seances');
            $table->foreign('session_formation_id')->references('id')->on('session_formations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seances_session_formation');
    }
};
