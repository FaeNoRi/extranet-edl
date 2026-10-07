<?php

namespace App\Console\Commands;

use App\Services\PreflightService;
use Illuminate\Console\Command;

class VerifierProduction extends Command
{
    protected $signature = 'edl:preflight';

    protected $description = 'Vérifie que l\'installation est prête pour la production (configuration, sécurité, sauvegardes, mentions légales)';

    public function handle(PreflightService $service): int
    {
        $controles = $service->controles();

        $this->table(
            ['Contrôle', 'Résultat', 'Détail'],
            collect($controles)->map(fn ($x) => [
                $x['nom'],
                match ($x['statut']) {
                    PreflightService::OK => '<info>OK</info>',
                    PreflightService::AVERTISSEMENT => '<comment>À traiter</comment>',
                    default => '<error>BLOQUANT</error>',
                },
                $x['statut'] === PreflightService::OK ? '' : $x['detail'],
            ])->all()
        );

        $bloquants = collect($controles)->where('statut', PreflightService::BLOQUANT)->count();
        $avertissements = collect($controles)->where('statut', PreflightService::AVERTISSEMENT)->count();

        if ($bloquants > 0) {
            $this->error("{$bloquants} point(s) bloquant(s), {$avertissements} à traiter : ne pas ouvrir l'extranet.");

            return self::FAILURE;
        }

        $this->info($avertissements > 0 ? "Aucun point bloquant, {$avertissements} à traiter." : 'Tout est en ordre.');

        return self::SUCCESS;
    }
}
