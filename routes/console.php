<?php

use App\Services\TachesPlanifieesService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tâches déclarées dans l'ordre chronologique : sur l'hébergement mutualisé OVH, le cron ne passe
// qu'une fois par heure et `edl:taches` rattrape les tâches échues dans cet ordre (la sauvegarde
// doit précéder les purges). Ailleurs, `schedule:run` chaque minute les lance à l'heure dite.

// Sauvegarde quotidienne (base + fichiers déposés) avant les purges de 3h, nettoyage des anciennes
// archives, puis contrôle qu'une sauvegarde récente existe (alerte e-mail sinon).
Schedule::command('backup:run')->dailyAt('02:30');
Schedule::command('backup:clean')->dailyAt('02:50');

// Signalement quotidien des comptes à purger (OP et FPC) selon les règles
// calendaires — aucune suppression automatique, validation manuelle dans
// l'administration (Purges).
Schedule::command('edl:purge-comptes')->dailyAt('03:00');

// Fichiers GESCOF téléversés mais jamais appliqués : supprimés après edl.imports.conservation_fichier_jours.
Schedule::command('edl:purge-imports')->dailyAt('03:15');

// Durée de conservation du journal (3 ans, cf. config activitylog.clean_after_days
// et la politique de confidentialité) : sans cette tâche, rien ne le purge.
Schedule::command('activitylog:clean')->dailyAt('03:30');

Schedule::command('backup:monitor')->dailyAt('08:00');

// Témoin de vie du planificateur : si le cron n'est pas (ou plus) en place, les sauvegardes et
// les purges ne tournent pas, sans aucune erreur visible. `edl:preflight` lit ce témoin
// (également écrit par `edl:taches` à chaque passage horaire).
Schedule::call(fn () => Cache::put(TachesPlanifieesService::TEMOIN, now(), now()->addDay()))
    ->everyMinute()
    ->name('edl-temoin-planificateur');
