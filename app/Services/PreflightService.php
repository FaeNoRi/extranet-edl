<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Contrôles à passer avant d'ouvrir l'extranet à de vrais utilisateurs (et après chaque
 * déploiement) : `php artisan edl:preflight`. Chaque contrôle renvoie un statut :
 * « ok », « avertissement » (à traiter, non bloquant) ou « bloquant ».
 */
class PreflightService
{
    public const OK = 'ok';

    public const AVERTISSEMENT = 'avertissement';

    public const BLOQUANT = 'bloquant';

    /** Extensions PHP nécessaires (PDF, archives ZIP, images, base MySQL…). */
    public const EXTENSIONS = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'gd', 'intl', 'mbstring', 'openssl', 'pdo_mysql', 'tokenizer', 'xml', 'zip'];

    /** @return array<int, array{nom: string, statut: string, detail: string}> */
    public function controles(): array
    {
        $c = [];
        $ajouter = function (string $nom, bool $ok, string $detail, string $siEchec = self::BLOQUANT) use (&$c) {
            $c[] = ['nom' => $nom, 'statut' => $ok ? self::OK : $siEchec, 'detail' => $detail];
        };

        // --- Application ---------------------------------------------------------------------
        $ajouter('Environnement', app()->environment('production'), 'APP_ENV = '.app()->environment().' (attendu : production).');
        $ajouter('Mode debug désactivé', ! config('app.debug'), 'APP_DEBUG doit être false : le mode debug affiche le code et la configuration.');
        $ajouter('Clé d\'application', filled(config('app.key')), 'APP_KEY est vide (php artisan key:generate).');
        $ajouter('Adresse en HTTPS', str_starts_with((string) config('app.url'), 'https://'), 'APP_URL = '.config('app.url').' (attendu : https://…).');
        $ajouter('Cookie de session sécurisé', (bool) config('session.secure'), 'SESSION_SECURE_COOKIE doit être true (HTTPS).');
        $ajouter('Journaux à rotation', $this->journalTourne(), 'Utiliser LOG_STACK=daily (LOG_DAILY_DAYS=14) : un journal unique grossit sans limite et conserve des données personnelles.', self::AVERTISSEMENT);

        // --- Base de données -----------------------------------------------------------------
        try {
            DB::connection()->getPdo();
            $ajouter('Base de données', true, '');
            $ajouter('Migrations à jour', $this->migrationsAJour(), 'Des migrations ne sont pas appliquées : bouton « Mettre à jour la base » de la page Serveur (ou php artisan migrate --force).');
            $ajouter('Aucun compte de démonstration', ! $this->compteAvecMotDePasseParDefaut(), 'Un compte administrateur utilise le mot de passe par défaut des données de démonstration.');
            $ajouter('Au moins un administrateur', User::where('role', 'admin')->exists(), 'Créer le premier administrateur : php artisan edl:creer-admin.');
        } catch (Throwable $e) {
            $ajouter('Base de données', false, 'Connexion ou lecture impossible : '.$e->getMessage());
        }

        // --- Courrier --------------------------------------------------------------------------
        $ajouter('Envoi d\'e-mails réel', ! in_array(config('mail.default'), ['log', 'array'], true), 'MAIL_MAILER = '.config('mail.default').' : les liens d\'accès ne seraient pas envoyés.');

        $ajouter('Envois aux stagiaires activés', (bool) config('edl.envois.stagiaires'), 'EDL_ENVOIS_STAGIAIRES=false : verrou de la phase de test, les stagiaires ne recevraient pas leur lien d\'accès. Passer à true à l\'ouverture.', self::AVERTISSEMENT);

        // --- Fichiers ----------------------------------------------------------------------------
        foreach (['storage/app', 'storage/framework/cache', 'storage/logs'] as $dossier) {
            $ajouter("Écriture dans {$dossier}", is_writable(base_path($dossier)), "Le dossier {$dossier} doit être accessible en écriture par le serveur web.");
        }
        $manquantes = array_values(array_filter(self::EXTENSIONS, fn (string $e) => ! extension_loaded($e)));
        $ajouter('Extensions PHP', $manquantes === [], 'Extensions manquantes : '.implode(', ', $manquantes).'.');

        // --- Sauvegardes -----------------------------------------------------------------------
        $ajouter('Chiffrement des sauvegardes', filled(config('backup.backup.password')), 'BACKUP_ARCHIVE_PASSWORD est vide : les archives contiendraient des données personnelles non chiffrées.');
        $ajouter('mysqldump disponible', $this->mysqldumpDisponible(), 'mysqldump est introuvable (DB_DUMP_BINARY_PATH) : la sauvegarde de la base échouera.');
        $ajouter('Sauvegarde hors serveur', count(config('backup.backup.destination.disks', [])) > 1, 'BACKUP_OFFSITE_DISK est vide : une panne du serveur emporterait aussi les sauvegardes.', self::AVERTISSEMENT);
        $ajouter('Planificateur actif', $this->planificateurActif(), 'Aucun passage du planificateur depuis 2 h : cron « * * * * * php artisan schedule:run », ou sur l\'hébergement mutualisé OVH tâche horaire « extranet/cron.php » (sauvegardes, purges). Normal juste après la mise en place.', self::AVERTISSEMENT);

        // --- Informations légales ------------------------------------------------------------
        $ajouter('Référent RGPD renseigné', filled(config('edl.legal.dpo_nom')) && filled(config('edl.legal.dpo_email')), 'EDL_DPO_NOM / EDL_DPO_EMAIL manquants (politique de confidentialité).', self::AVERTISSEMENT);
        $ajouter('Directeur de publication', filled(config('edl.structure.directeur_publication')), 'EDL_DIRECTEUR_PUBLICATION manquant (mentions légales).', self::AVERTISSEMENT);
        $ajouter('N° de déclaration d\'activité', filled(config('edl.structure.nda')), 'EDL_NDA manquant (mentions légales).', self::AVERTISSEMENT);
        $ajouter('Registre RGPD validé', filled(config('edl.legal.registre_valide_le')), 'EDL_REGISTRE_VALIDE_LE vide : le registre est encore présenté comme un projet.', self::AVERTISSEMENT);

        // --- Performance et serveur ------------------------------------------------------------
        $ajouter('Configuration en cache', app()->configurationIsCached(), 'Lancer php artisan optimize après chaque déploiement (sans objet sur l\'hébergement mutualisé sans SSH : OPcache suffit).', self::AVERTISSEMENT);
        $ajouter('OPcache', (bool) ini_get('opcache.enable'), 'Activer OPcache dans php.ini.', self::AVERTISSEMENT);
        $ajouter('Limites de téléversement PHP', $this->limitesUploadSuffisantes(), 'upload_max_filesize / post_max_size inférieurs à EDL_UPLOAD_MAX_KO ('.config('edl.uploads.taille_max_ko').' Ko).', self::AVERTISSEMENT);
        $ajouter('Proxy de confiance', filled(config('trustedproxy.proxies')), 'TRUSTED_PROXIES vide : à renseigner si l\'hébergeur place l\'extranet derrière un reverse proxy (sinon les liens peuvent être générés en http://).', self::AVERTISSEMENT);

        return $c;
    }

    /** @param  array<int, array{statut: string}>  $controles */
    public function aDesBloquants(array $controles): bool
    {
        return collect($controles)->contains(fn ($x) => $x['statut'] === self::BLOQUANT);
    }

    private function journalTourne(): bool
    {
        return config('logging.default') === 'daily'
            || in_array('daily', (array) config('logging.channels.stack.channels', []), true);
    }

    private function migrationsAJour(): bool
    {
        return $this->migrationsEnAttente() === [];
    }

    /** @return list<string> migrations présentes dans le code mais pas encore appliquées à la base */
    public function migrationsEnAttente(): array
    {
        $fichiers = collect(glob(database_path('migrations/*.php')))->map(fn ($f) => basename($f, '.php'));
        $appliquees = DB::table('migrations')->pluck('migration');

        return $fichiers->diff($appliquees)->sort()->values()->all();
    }

    private function compteAvecMotDePasseParDefaut(): bool
    {
        return User::where('role', 'admin')->get()->contains(fn (User $u) => Hash::check('password', $u->password));
    }

    private function mysqldumpDisponible(): bool
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'], true)) {
            return true; // Pas de dump MySQL à réaliser.
        }

        // Lu dans la configuration (DB_DUMP_BINARY_PATH) : env() renvoie null une fois la configuration en cache.
        $dossier = rtrim((string) config('database.connections.'.config('database.default').'.dump.dump_binary_path'), '/\\');
        $binaire = $dossier !== '' ? $dossier.DIRECTORY_SEPARATOR.'mysqldump' : 'mysqldump';

        try {
            return Process::run([$binaire, '--version'])->successful();
        } catch (Throwable) {
            return false;
        }
    }

    private function planificateurActif(): bool
    {
        // Cron chaque minute (schedule:run) ou une fois par heure (edl:taches, hébergement mutualisé).
        $dernier = TachesPlanifieesService::dernierPassage();

        return $dernier !== null && now()->diffInMinutes($dernier, true) <= 120;
    }

    private function limitesUploadSuffisantes(): bool
    {
        $max = (int) config('edl.uploads.taille_max_ko') * 1024;

        return $this->octets((string) ini_get('upload_max_filesize')) >= $max
            && $this->octets((string) ini_get('post_max_size')) >= $max;
    }

    private function octets(string $valeur): int
    {
        $valeur = trim($valeur);
        if ($valeur === '' || $valeur === '-1') {
            return PHP_INT_MAX;
        }
        $n = (int) $valeur;

        return match (strtolower(substr($valeur, -1))) {
            'g' => $n * 1024 ** 3,
            'm' => $n * 1024 ** 2,
            'k' => $n * 1024,
            default => $n,
        };
    }
}
