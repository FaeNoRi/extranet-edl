<?php

use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

/*
 * Sauvegardes (spatie/laravel-backup) : base de données + fichiers déposés
 * (storage/app/private : documents, ressources, fiches PDF ; storage/app/public :
 * photos des formateurs). Le .env n'est PAS sauvegardé : les secrets se
 * conservent à part (gestionnaire de mots de passe). Voir CLAUDE.md et
 * docs/sauvegardes.md pour la procédure de restauration.
 */
// Séparateurs normalisés : sous Windows storage_path('app/private') mélange \ et /, ce qui fausse
// le calcul des chemins relatifs dans l'archive.
$stockage = storage_path('app');
$sep = DIRECTORY_SEPARATOR;

return [

    'backup' => [
        'name' => 'extranet-edl',

        'source' => [
            'files' => [
                'include' => [
                    $stockage.$sep.'private',
                    $stockage.$sep.'public',
                ],
                'exclude' => [
                    // Fichiers GESCOF téléversés : temporaires (purgés sous 7 jours), pas de raison de les conserver.
                    $stockage.$sep.'private'.$sep.'gescof',
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => $stockage,
            ],
            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_timestamp_format' => null,
        'database_dump_filename_base' => 'database',
        'database_dump_file_extension' => '',

        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9,
            'filename_prefix' => '',
            // Disque local (storage/app/backups) + disque hors-site facultatif : une sauvegarde
            // qui reste sur le même serveur ne protège pas d'une panne ou d'un sinistre du serveur.
            'disks' => array_values(array_filter(['backups', env('BACKUP_OFFSITE_DISK')])),
            'continue_on_failure' => false,
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        // L'archive contient des données personnelles : chiffrée (AES-256) dès qu'un mot de passe est défini.
        'password' => env('BACKUP_ARCHIVE_PASSWORD') ?: null,
        'encryption' => 'default',

        'verify_backup' => false,
        'tries' => 1,
        'retry_delay' => 0,
    ],

    // Uniquement les échecs : une alerte qui arrive tous les jours finit ignorée.
    'notifications' => [
        'notifications' => [
            BackupHasFailedNotification::class => ['mail'],
            UnhealthyBackupWasFoundNotification::class => ['mail'],
            CleanupHasFailedNotification::class => ['mail'],
        ],
        'notifiable' => Notifiable::class,
        'mail' => [
            // « ?: » et non une valeur par défaut d'env() : une variable déclarée vide (.env.example, CI)
            // vaut '' et non null, ce que spatie refuse comme adresse.
            'to' => env('BACKUP_NOTIFICATION_EMAIL') ?: env('EDL_EMAIL') ?: 'contact@edl-grandcalais.com',
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS') ?: 'no-reply@edl-grandcalais.fr',
                'name' => env('MAIL_FROM_NAME') ?: 'Extranet EDL+',
            ],
        ],
        'slack' => ['webhook_url' => '', 'channel' => null, 'username' => null, 'icon' => null],
        'discord' => ['webhook_url' => '', 'username' => '', 'avatar_url' => ''],
        'webhook' => ['url' => ''],
    ],

    'log_channel' => null,

    // Alerte si la dernière sauvegarde a plus d'un jour ou si le stockage dépasse 5 Go.
    'monitor_backups' => [
        [
            'name' => 'extranet-edl',
            'disks' => array_values(array_filter(['backups', env('BACKUP_OFFSITE_DISK')])),
            'health_checks' => [
                MaximumAgeInDays::class => 1,
                MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    /*
     * Conservation : les sauvegardes contiennent aussi des comptes déjà purgés, qui n'y
     * disparaissent qu'à leur propre expiration. On garde donc peu de temps : toutes pendant
     * 7 jours puis une par jour pendant 21 jours (28 jours au total), rien de plus ancien.
     * Durée à valider avec l'EDL (registre des traitements) ; à tenir cohérente avec la
     * politique de confidentialité.
     */
    'cleanup' => [
        'strategy' => DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 21,
            'keep_weekly_backups_for_weeks' => 0,
            'keep_monthly_backups_for_months' => 0,
            'keep_yearly_backups_for_years' => 0,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
        'tries' => 1,
        'retry_delay' => 0,
    ],

];
