<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Séance réalisée à distance (retour de réunion d'octobre 2026) : indiqué par le formateur
 * sur la fiche pédagogique, mentionné « En distanciel » sur le PDF généré.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->boolean('distanciel')->default(false)->after('date');
        });
    }

    public function down(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->dropColumn('distanciel');
        });
    }
};
