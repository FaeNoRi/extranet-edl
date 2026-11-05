<?php

/*
 * Point d'entrée de la tâche planifiée d'un hébergement mutualisé OVH (sans SSH) : dans l'espace client,
 * Hébergement > Plus > Cron, commande « extranet/cron.php » (chemin depuis la racine FTP), PHP 8.4,
 * toutes les heures. Équivaut à « php artisan edl:taches ». Hors du dossier public : non appelable
 * depuis le web. Voir docs/deploiement-ovh.md.
 */

use Illuminate\Foundation\Application;
use Symfony\Component\Console\Input\ArgvInput;

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

exit($app->handleCommand(new ArgvInput(['artisan', 'edl:taches'])));
