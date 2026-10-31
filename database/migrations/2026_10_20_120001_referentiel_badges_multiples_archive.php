<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trame du référentiel v3 (octobre 2026) :
 *  - le module devient un texte (la liste vit dans Referentiel::MODULES, plus dans la base) ;
 *  - badges multiples (JUNIOR, ORAL, COM…) au lieu d'un badge en texte libre ;
 *  - archivage : une entrée retirée de la trame mais cochée dans d'anciennes fiches est gardée,
 *    sans être proposée aux formateurs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referentiel', function (Blueprint $table) {
            $table->string('module')->change();
            $table->json('badges')->nullable()->after('contenu');
            $table->timestamp('archive_le')->nullable()->after('niveaux');
        });

        DB::table('referentiel')->whereNotNull('badge')->where('badge', '!=', '')->orderBy('id')->each(function ($ligne) {
            $badges = array_values(array_unique(array_filter(array_map(
                fn ($b) => mb_strtoupper(trim($b)), explode(',', $ligne->badge)
            ))));
            DB::table('referentiel')->where('id', $ligne->id)->update(['badges' => json_encode($badges)]);
        });

        Schema::table('referentiel', function (Blueprint $table) {
            $table->dropColumn('badge');
        });
    }

    public function down(): void
    {
        Schema::table('referentiel', function (Blueprint $table) {
            $table->string('badge')->nullable()->after('contenu');
        });

        DB::table('referentiel')->whereNotNull('badges')->orderBy('id')->each(function ($ligne) {
            DB::table('referentiel')->where('id', $ligne->id)->update(['badge' => implode(', ', json_decode($ligne->badges, true) ?: [])]);
        });

        Schema::table('referentiel', function (Blueprint $table) {
            $table->dropColumn(['badges', 'archive_le']);
        });
    }
};
