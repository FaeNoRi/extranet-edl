<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_formations', function (Blueprint $table) {
            // Code stage brut de l'export GESCOF (ex. AN-OP-8-9) : le code_produit
            // FPC/OP en est dérivé.
            $table->string('code_stage')->nullable()->after('nom');
            // Colonne « ListeItv » de l'export, conservée telle quelle : format
            // libre, saisi/modifié manuellement — sert de référence à l'admin
            // pour compléter l'affectation des formateurs.
            $table->text('intervenants_import')->nullable()->after('formateur_id');
            $table->timestamp('gescof_importe_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('session_formations', function (Blueprint $table) {
            $table->dropColumn(['code_stage', 'intervenants_import', 'gescof_importe_at']);
        });
    }
};
