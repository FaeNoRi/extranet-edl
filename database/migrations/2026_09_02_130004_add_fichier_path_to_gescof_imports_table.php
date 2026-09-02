<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gescof_imports', function (Blueprint $table) {
            // Chemin du fichier téléversé, conservé entre la simulation et
            // l'application depuis l'interface d'administration.
            $table->string('fichier_path')->nullable()->after('fichier_nom');
        });
    }

    public function down(): void
    {
        Schema::table('gescof_imports', function (Blueprint $table) {
            $table->dropColumn('fichier_path');
        });
    }
};
