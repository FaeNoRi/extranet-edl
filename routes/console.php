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
