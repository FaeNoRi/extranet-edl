<?php

use App\Support\NiveauxReferentiel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retours de la réunion d'octobre 2026 sur le référentiel :
 *  - niveaux EDL (essentiel / consolidation / perfectionnement) à la place de A1…C2 ;
 *  - langue de l'entrée (nulle = toutes langues) : un même code peut exister dans plusieurs langues ;
 *  - badge facultatif en texte libre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referentiel', function (Blueprint $table) {
            $table->string('langue')->nullable()->after('module');
            $table->string('badge')->nullable()->after('contenu');
            $table->dropUnique('referentiel_code_unique');
            $table->unique(['code', 'langue'], 'referentiel_code_langue_unique');
        });

        DB::table('referentiel')->orderBy('id')->each(function ($ligne) {
            $anciens = $ligne->niveaux ? explode(',', $ligne->niveaux) : [];
            DB::table('referentiel')->where('id', $ligne->id)->update([
                'niveaux' => implode(',', NiveauxReferentiel::depuisCecrl($anciens)),
            ]);
        });
    }

    public function down(): void
    {
        $retour = ['essentiel' => 'A1,A2', 'consolidation' => 'B1,B2', 'perfectionnement' => 'C1,C2'];
        DB::table('referentiel')->orderBy('id')->each(function ($ligne) use ($retour) {
            $cles = $ligne->niveaux ? explode(',', $ligne->niveaux) : [];
            DB::table('referentiel')->where('id', $ligne->id)->update([
                'niveaux' => implode(',', array_map(fn ($c) => $retour[$c] ?? $c, $cles)),
            ]);
        });

        Schema::table('referentiel', function (Blueprint $table) {
            $table->dropUnique('referentiel_code_langue_unique');
            $table->unique('code', 'referentiel_code_unique');
            $table->dropColumn(['langue', 'badge']);
        });
    }
};
