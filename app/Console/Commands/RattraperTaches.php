<?php

namespace App\Console\Commands;

use App\Services\TachesPlanifieesService;
use Illuminate\Console\Command;

class RattraperTaches extends Command
{
    protected $signature = 'edl:taches';

    protected $description = 'Exécute les tâches planifiées dont l\'échéance est passée (cron horaire d\'un hébergement mutualisé)';

    public function handle(TachesPlanifieesService $service): int
    {
        $executees = $service->rattraper();

        if ($executees === []) {
            $this->info('Aucune tâche à exécuter.');
        }

        foreach ($executees as $tache) {
            $this->line(($tache['ok'] ? '<info>OK</info>     ' : '<error>ÉCHEC</error>  ').$tache['commande']);
        }

        return collect($executees)->every('ok') ? self::SUCCESS : self::FAILURE;
    }
}
