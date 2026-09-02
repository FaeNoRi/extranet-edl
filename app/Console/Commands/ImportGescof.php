<?php

namespace App\Console\Commands;

use App\Services\Gescof\GescofImporter;
use App\Services\Gescof\GescofImportReport;
use Illuminate\Console\Command;

class ImportGescof extends Command
{
    protected $signature = 'edl:import-gescof
                            {fichier : Chemin du fichier GESCOF (.xlsx ou .csv)}
                            {--appliquer : Applique réellement l\'import (sinon simulation)}
                            {--envoyer-acces : Envoie les liens d\'accès aux comptes créés}';

    protected $description = "Importe l'export GESCOF des inscriptions (simulation par défaut)";

    public function handle(GescofImporter $importer): int
    {
        $fichier = $this->argument('fichier');

        if (! is_file($fichier)) {
            $this->error("Fichier introuvable : {$fichier}");

            return self::FAILURE;
        }

        $appliquer = (bool) $this->option('appliquer');

        $this->info($appliquer ? 'Import GESCOF — application' : 'Import GESCOF — simulation (aucune écriture)');

        $rapport = $appliquer
            ? $importer->appliquer($fichier, null, (bool) $this->option('envoyer-acces'))
            : $importer->simuler($fichier);

        $this->afficherRapport($rapport);

        return self::SUCCESS;
    }

    private function afficherRapport(GescofImportReport $rapport): void
    {
        $this->newLine();
        $this->table(['Indicateur', 'Valeur'], [
            ['Lignes lues', $rapport->lignesLues],
            ['Lignes ignorées', $rapport->lignesIgnorees],
            ['Sessions créées', $rapport->sessionsCreees],
            ['Sessions mises à jour', $rapport->sessionsMaj],
            ['Comptes créés', $rapport->comptesCrees],
            ['Comptes réactivés', $rapport->comptesReactives],
            ['Comptes disparus (marqués)', $rapport->comptesDisparus],
            ['Comptes à notifier', count($rapport->comptesANotifier)],
        ]);

        if ($rapport->anomalies !== []) {
            $this->newLine();
            $this->warn(count($rapport->anomalies).' anomalie(s) :');
            foreach ($rapport->anomalies as $a) {
                $ligne = $a['ligne'] ? "L{$a['ligne']} " : '';
                $this->line("  - {$ligne}[{$a['type']}] {$a['message']}");
            }
        }

        if (! $rapport->applique) {
            $this->newLine();
            $this->comment('Simulation : relancer avec --appliquer pour écrire.');
        }
    }
}
