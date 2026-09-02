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
        Schema::create('session_formation_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('session_formation_id');
            $table->foreignId('user_id')->constrained('users');

            $table->foreign('session_formation_id')->references('id')->on('session_formations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_formation_user');
    }
};
