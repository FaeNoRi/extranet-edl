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
        Schema::create('session_formations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('num_GESCOF');
            $table->string('nom');
            $table->enum('code_produit', ['FPC', 'OP'])->default('OP');
            $table->text('objectifs');
            $table->boolean('distanciel')->default(false);
            $table->string('lien_teams')->nullable()->comment("Possiblement auto-généré ?");
            $table->string('client');
            $table->text('dates_planning')->comment("Export d'une liste de dates depuis GESCOF");
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_formations');
    }
};
