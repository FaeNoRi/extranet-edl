<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Purge des comptes selon les règles calendaires (OP appliquée, FPC signalée).
Schedule::command('edl:purge-comptes --appliquer')->dailyAt('03:00');
