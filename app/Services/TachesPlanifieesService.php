<?php

namespace App\Services;

use Cron\CronExpression;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Exécution des tâches planifiées sur un hébergement mutualisé (OVH) : le cron n'y passe qu'une fois
 * par heure, à une minute imposée (xx:36…), alors que `schedule:run` ne lance que les tâches dues à la
 * minute exacte de son passage — aucune ne partirait.
 *
 * `edl:taches` (appelée par `cron.php`) rattrape donc chaque tâche de `routes/console.php` dont la
 * dernière échéance n'a pas encore été exécutée, dans l'ordre de déclaration et dans le même
 * processus (pas de sous-processus PHP). Une tâche manquée (cron en panne) part au passage suivant.
 */
class TachesPlanifieesService
{
    public const TEMOIN = 'edl:planificateur:dernier-passage';

    public const RAPPORT = 'edl:taches:dernier-rapport';

    public function __construct(private Schedule $schedule) {}

    /**
     * @return list<array{commande: string, ok: bool, sortie: string}> tâches exécutées à ce passage
     */
    public function rattraper(): array
    {
        Cache::put(self::TEMOIN, now(), now()->addDay());

        $executees = [];

        foreach ($this->schedule->events() as $event) {
            $commande = $this->commandeArtisan($event);

            if ($commande === null || str_starts_with($commande, 'edl:taches')) {
                continue; // Fonction anonyme (témoin, écrit ci-dessus) ou cette commande elle-même.
            }

            $echeance = Carbon::instance(
                (new CronExpression($event->expression))->getPreviousRunDate(now(), 0, true, $event->timezone ?? config('app.timezone'))
            );
            $cle = 'edl:taches:'.sha1($event->expression.' '.$commande);
            $derniere = Cache::get($cle);

            if ($derniere && Carbon::parse($derniere)->gte($echeance)) {
                continue;
            }

            // Marquée avant l'exécution : une tâche en échec n'est pas relancée toutes les heures
            // (l'échec est dans le rapport et le journal ; backup:monitor alerte si besoin).
            Cache::put($cle, now()->toIso8601String(), now()->addDays(40));
            $executees[] = $this->executer($commande);
        }

        if ($executees !== []) {
            Cache::put(self::RAPPORT, ['le' => now()->toIso8601String(), 'taches' => $executees], now()->addDays(40));
        }

        return $executees;
    }

    /** @return array{le: string, taches: list<array{commande: string, ok: bool, sortie: string}>}|null */
    public function dernierRapport(): ?array
    {
        return Cache::get(self::RAPPORT);
    }

    /**
     * Nom et arguments de la commande Artisan d'un événement (« backup:run --only-db »), null pour
     * une fonction anonyme. `Schedule::command()` stocke « 'php' 'artisan' backup:run ».
     */
    private function commandeArtisan(Event $event): ?string
    {
        if (! $event->command || ! preg_match('/artisan["\']?\s+(.+)$/', $event->command, $m)) {
            return null;
        }

        return trim($m[1]);
    }

    /** @return array{commande: string, ok: bool, sortie: string} */
    private function executer(string $commande): array
    {
        try {
            $ok = Artisan::call($commande) === 0;
            $sortie = Artisan::output();
        } catch (Throwable $e) {
            $ok = false;
            $sortie = $e->getMessage();
        }

        if (! $ok) {
            Log::error("Tâche planifiée en échec : {$commande}", ['sortie' => mb_substr($sortie, 0, 2000)]);
        }

        return ['commande' => $commande, 'ok' => $ok, 'sortie' => mb_substr(trim($sortie), 0, 2000)];
    }
}
