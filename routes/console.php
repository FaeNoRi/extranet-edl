<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Signalement quotidien des comptes à purger (OP et FPC) selon les règles
// calendaires — aucune suppression automatique, validation manuelle dans
// l'administration (Purges).
Schedule::command('edl:purge-comptes')->dailyAt('03:00');

// Durée de conservation du journal (3 ans, cf. config activitylog.clean_after_days
// et la politique de confidentialité) : sans cette tâche, rien ne le purge.
Schedule::command('activitylog:clean')->dailyAt('03:30');

// Fichiers GESCOF téléversés mais jamais appliqués : supprimés après edl.imports.conservation_fichier_jours.
Schedule::command('edl:purge-imports')->dailyAt('03:15');

// Sauvegarde quotidienne (base + fichiers déposés) avant les purges de 3h, nettoyage des anciennes
// archives, puis contrôle qu'une sauvegarde récente existe (alerte e-mail sinon).
Schedule::command('backup:run')->dailyAt('02:30');
Schedule::command('backup:clean')->dailyAt('02:50');
Schedule::command('backup:monitor')->dailyAt('08:00');
