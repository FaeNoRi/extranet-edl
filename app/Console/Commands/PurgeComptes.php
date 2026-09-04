<?php

namespace App\Console\Commands;

use App\Services\PurgeComptesService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeComptes extends Command
{
    protected $signature = 'edl:purge-comptes
                            {--op : Purge les comptes OP (fermeture estivale)}
                            {--fpc : Purge les comptes FPC (formations terminées en N-1)}
                            {--appliquer : Exécute la suppression (sinon simulation)}';

    protected $description = 'Purge les comptes stagiaires selon les règles du cahier des charges';

    public function handle(PurgeComptesService $service): int
    {
        $op = $this->option('op');
        $fpc = $this->option('fpc');
        $appliquer = (bool) $this->option('appliquer');
        $calendaire = ! $op && ! $fpc;

        // Sans option : règles calendaires. La purge OP peut s'appliquer
        // automatiquement ; la purge FPC est seulement signalée (le CDC la
        // veut déclenchée manuellement par l'admin).
        if ($calendaire) {
            $op = $this->dateAtteinte(config('edl.purges.op_apres'));
            $fpc = $this->dateAtteinte(config('edl.purges.fpc_le'));

            if (! $op && ! $fpc) {
                $this->info('Aucune purge planifiée à cette date.');

                return self::SUCCESS;
            }
        }

        $total = 0;

        if ($op) {
            $total += $this->traiter('OP', $service->comptesOpAPurger(), 'fermeture estivale (comptes OP)', $appliquer, $service);
        }

        if ($fpc) {
            $appliquerFpc = $appliquer && ! $calendaire;
            $total += $this->traiter('FPC', $service->comptesFpcAPurger(), 'formations FPC terminées en N-1', $appliquerFpc, $service);

            if ($calendaire && $service->comptesFpcAPurger()->isNotEmpty()) {
                $this->warn('Purge FPC à valider manuellement dans l\'administration (Purges).');
            }
        }

        if (! $appliquer && $total > 0) {
            $this->newLine();
            $this->comment('Simulation : relancer avec --appliquer pour supprimer.');
        }

        return self::SUCCESS;
    }

    private function traiter(string $libelle, $comptes, string $motif, bool $appliquer, PurgeComptesService $service): int
    {
        $this->line("<info>{$libelle}</info> : ".$comptes->count().' compte(s) concerné(s).');

        foreach ($comptes->take(30) as $compte) {
            $this->line("  - {$compte->login}  ({$compte->nom_complet})");
        }
        if ($comptes->count() > 30) {
            $this->line('  … et '.($comptes->count() - 30).' de plus');
        }

        if ($appliquer) {
            $n = $service->supprimer($comptes, $motif);
            $this->info("  → {$n} compte(s) supprimé(s).");
        }

        return $comptes->count();
    }

    private function dateAtteinte(string $moisJour): bool
    {
        [$mois, $jour] = array_map('intval', explode('-', $moisJour));

        return Carbon::now()->gte(Carbon::create(Carbon::now()->year, $mois, $jour)->startOfDay());
    }
}
