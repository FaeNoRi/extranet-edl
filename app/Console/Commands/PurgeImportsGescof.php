<?php

namespace App\Console\Commands;

use App\Services\PurgeImportsGescofService;
use Illuminate\Console\Command;

class PurgeImportsGescof extends Command
{
    protected $signature = 'edl:purge-imports {--jours= : Âge minimal en jours (défaut : config edl.imports.conservation_fichier_jours)}';

    protected $description = 'Supprime les fichiers d\'import GESCOF téléversés mais jamais appliqués';

    public function handle(PurgeImportsGescofService $service): int
    {
        $jours = (int) ($this->option('jours') ?? config('edl.imports.conservation_fichier_jours'));

        $n = $service->purger($jours);

        $this->info("{$n} fichier(s) d'import non appliqué(s) de plus de {$jours} jour(s) supprimé(s).");

        return self::SUCCESS;
    }
}
