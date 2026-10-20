<?php

namespace App\Console\Commands;

use App\Models\Referentiel;
use Database\Seeders\ReferentielSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Charge la trame du référentiel (Database\Seeders\ReferentielSeeder) sur une base existante :
 *  - entrées de la trame : créées ou mises à jour (et réactivées si elles étaient archivées) ;
 *  - autres entrées : archivées si elles sont cochées dans une séance ou portent des documents
 *    (l'historique des fiches pédagogiques reste intact), supprimées sinon.
 */
class ChargerTrameReferentiel extends Command
{
    protected $signature = 'edl:referentiel-charger
                            {--simuler : Affiche ce qui serait fait, sans rien modifier}';

    protected $description = 'Charge la trame actuelle du référentiel et archive les entrées qui n\'en font plus partie';

    public function handle(): int
    {
        $simulation = (bool) $this->option('simuler');
        $codes = ReferentielSeeder::codes();
        $bilan = ['creees' => 0, 'mises_a_jour' => 0, 'archivees' => 0, 'supprimees' => 0];

        DB::beginTransaction();

        try {
            $horsTrame = Referentiel::query()
                ->withCount(['seances', 'ressources'])
                ->where(fn ($q) => $q->where('langue', '!=', ReferentielSeeder::LANGUE)
                    ->orWhereNull('langue')
                    ->orWhereNotIn('code', $codes))
                ->get();

            foreach ($horsTrame as $entree) {
                if ($entree->seances_count > 0 || $entree->ressources_count > 0) {
                    if (! $entree->estArchivee()) {
                        $entree->update(['archive_le' => now()]);
                        $bilan['archivees']++;
                    }
                } else {
                    $entree->delete();
                    $bilan['supprimees']++;
                }
            }

            $existants = Referentiel::where('langue', ReferentielSeeder::LANGUE)->whereIn('code', $codes)->count();
            (new ReferentielSeeder)->run();
            $bilan['mises_a_jour'] = $existants;
            $bilan['creees'] = count($codes) - $existants;

            if ($simulation) {
                DB::rollBack();
            } else {
                activity('Référentiel')->withProperties($bilan)->log('Chargement de la trame du référentiel ('.count($codes).' entrées)');
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->table(['Créées', 'Mises à jour', 'Archivées (cochées dans des séances)', 'Supprimées (inutilisées)'], [array_values($bilan)]);
        $this->info($simulation ? 'Simulation : rien n\'a été modifié.' : 'Trame du référentiel chargée.');

        return self::SUCCESS;
    }
}
