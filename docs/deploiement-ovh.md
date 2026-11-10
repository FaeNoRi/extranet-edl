# Déploiement sur l'hébergement mutualisé OVH (offre Perso, sans SSH)

Hébergement retenu par l'EDL : OVH **Perso**, en multisite (dossiers `www`, `plannEDL` et `extranet` à la racine
FTP), domaine `extranet.ecoledeslangues-grandcalais.fr` (certificat Let's Encrypt), base `ecoledyextranet`
sur `ecoledyextranet.mysql.db`. Ce document remplace [`deploiement.md`](deploiement.md) pour ce serveur ; les
principes (préflight, sauvegardes, exploitation) restent ceux de ce document et de [`sauvegardes.md`](sauvegardes.md).

## Ce qui change sans SSH

| Besoin | Sur un serveur classique | Sur OVH Perso |
|---|---|---|
| Dépendances PHP, assets | `composer install`, `npm run build` sur le serveur | sur le poste : `scripts/paquet-ovh.sh`, puis dépôt FTP |
| Création de la base | `php artisan migrate --seed` | script SQL généré sur le poste (`scripts/sql-installation.sh`), importé dans phpMyAdmin |
| Premier administrateur | `php artisan edl:creer-admin` | compte « installation » du script SQL, puis Admin > **Administrateurs** |
| Liens d'accès (e-mails coupés) | `edl:acces` + lecture du jeton | bouton **Lien d'accès** des listes (lien affiché, à transmettre soi-même) |
| Mises à jour de la base | `php artisan migrate --force` | Admin > **Serveur** > « Mettre à jour la base » |
| Contrôle préalable | `php artisan edl:preflight` | Admin > **Serveur** (mêmes contrôles) |
| Tâches planifiées | cron `schedule:run` chaque minute | tâche OVH **horaire** sur `extranet/cron.php` (`edl:taches`) |
| Photos des formateurs | lien `public/storage` | servies par l'application (route `photos.formateur`) : rien à faire |
| Maintenance | `php artisan down` | fichier `storage/framework/down` déposé par FTP (§ 5) |
| Configuration en cache | `php artisan optimize` | sans objet (OPcache suffit) : l'avertissement « Configuration en cache » est normal |

Le cron d'OVH ne passe qu'**une fois par heure, à une minute imposée** (par exemple xx:36). `edl:taches` exécute à
chaque passage les tâches de `routes/console.php` dont l'heure est passée et qui n'ont pas encore tourné : la
sauvegarde de 2h30 part au passage de 2h36, les purges de 3h à 3h36, etc. Une heure manquée est rattrapée au
passage suivant.

## 1. Points à vérifier une fois dans l'espace client OVH

1. **Multisite** : le dossier racine de `extranet.ecoledeslangues-grandcalais.fr` doit être `extranet/public`
   (et non `extranet`) : rien d'autre que `public/` ne doit être accessible depuis internet (`.env`, `storage/`…).
2. **Version de PHP** : l'extranet exige **PHP 8.4**. OVH la règle par le fichier `.ovhconfig`. Le paquet en
   place un dans `extranet/` et dans `extranet/public/`, ce qui suffit d'après les retours d'utilisateurs ; la
   documentation d'OVH indique pourtant que seul celui de la **racine FTP** est lu. Après le premier dépôt,
   la page Admin > Serveur affiche la version réellement utilisée. Si elle est inférieure à 8.4, le site affiche
   une erreur « Composer detected issues in your platform » : il faut alors passer le `.ovhconfig` de la racine
   en 8.4, **ce qui change aussi la version de `www` et `plannEDL`** — vérifier d'abord leur compatibilité.
3. **Base de données** : relever la version MySQL (onglet Bases de données ; 8.0 attendu) et le mot de passe de
   l'utilisateur `ecoledyextranet` (à mettre seulement dans le `.env`).

## 2. Préparer sur le poste (Windows, Git Bash)

Variables communes (PHP par défaut du poste en 8.2) :

```sh
export PHP="C:/laragon/bin/php/php-8.4.7-nts-Win32-vs17-x64/php.exe"
export COMPOSER_CMD="$PHP C:/ProgramData/ComposerSetup/bin/composer.phar"
export MYSQL_BIN="C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin"
```

1. **Version** : `main` à jour, puis un tag (`git tag v0.9.0-test && git push origin v0.9.0-test`).
2. **Paquet FTP** :
   ```sh
   scripts/paquet-ovh.sh v0.9.0-test --htpasswd <dossier de l'application>/.htpasswd
   ```
   Résultat dans `paquets/extranet-v0.9.0-test/` (ignoré par git). `--htpasswd` protège tout le site par un
   mot de passe commun pendant le test ; le chemin est **absolu** (affiché par Admin > Serveur, « Dossier de
   l'application », ou dans l'espace client OVH). Sans le connaître au premier dépôt : empaqueter sans
   `--htpasswd`, relever le chemin sur la page Serveur (la base ne contient alors que le compte d'installation),
   puis redéposer `public/.htaccess` d'un paquet protégé.
3. **Mot de passe Basic Auth** (fichier `.htpasswd`, hors du dossier public) :
   ```sh
   "$PHP" -r 'echo "edl:".password_hash($argv[1], PASSWORD_BCRYPT).PHP_EOL;' 'mot-de-passe-commun' > paquets/.htpasswd
   ```
4. **Script SQL d'installation** (MySQL de Laragon démarré) :
   ```sh
   scripts/sql-installation.sh <votre e-mail>
   ```
   Produit `paquets/extranet-installation-AAAAMMJJ.sql` et **affiche une seule fois** le mot de passe du compte
   `installation` : le noter dans le gestionnaire de mots de passe.
5. **`.env`** : partir de `.env.production.example` et renseigner en particulier :
   ```ini
   APP_KEY=                       # "$PHP" artisan key:generate --show (à conserver, voir sauvegardes.md)
   APP_URL=https://extranet.ecoledeslangues-grandcalais.fr
   DB_HOST=ecoledyextranet.mysql.db
   DB_DATABASE=ecoledyextranet
   DB_USERNAME=ecoledyextranet
   DB_PASSWORD=<mot de passe de la base>
   DB_DUMP_BINARY_PATH=
   MAIL_MAILER=log                # phase de test : aucun e-mail
   EDL_ENVOIS_STAGIAIRES=false
   BACKUP_ARCHIVE_PASSWORD=<long et aléatoire, conservé hors du serveur>
   TRUSTED_PROXIES=               # voir § 3, étape 5
   ```

## 3. Première installation

1. **Base** : phpMyAdmin (espace client > Bases de données > `ecoledyextranet` > Accéder à phpMyAdmin) >
   Importer > le fichier `extranet-installation-….sql`. **Une seule fois, base vide** : le script recrée les tables.
2. **Fichiers** (FileZilla, FTP) : déposer **le contenu** de `paquets/extranet-v0.9.0-test/` dans `extranet/`, puis
   `.env` et `paquets/.htpasswd` dans `extranet/`. Environ 7 700 fichiers : compter plusieurs minutes.
3. **Tâche planifiée** : espace client > Hébergement > Plus > Cron > Ajouter : commande `extranet/cron.php`,
   langage **PHP 8.4**, fréquence toutes les heures, logs activés. (OVH la désactive après 10 échecs : à vérifier
   si la page Serveur ne montre plus de passage.)
4. **Connexion** : `https://extranet.ecoledeslangues-grandcalais.fr` (mot de passe commun, puis identifiant
   `installation`). Le site redirige `http://` vers `https://`.
5. **Admin > Serveur** :
   - « Version de PHP » = 8.4 ; contrôles : seul **« Envoi d'e-mails réel » est bloquant** (attendu pendant le test) ;
     « Configuration en cache », « Sauvegarde hors serveur », « Envois aux stagiaires », informations légales
     manquantes : à traiter, normaux à ce stade ;
   - « Connexion et PHP » : si un en-tête X-Forwarded-For apparaît et que la connexion n'est pas vue comme HTTPS,
     mettre l'adresse de connexion (ou `*`) dans `TRUSTED_PROXIES` du `.env`, redéposer le `.env`, recharger ;
   - limites `upload_max_filesize` / `post_max_size` : si elles sont inférieures à 50 Mo, baisser `EDL_UPLOAD_MAX_KO`
     dans le `.env` (OVH ne permet pas de les modifier).
6. **Comptes** : Admin > Administrateurs > créer les comptes nominatifs, « Lien d'accès », transmettre chaque lien
   à la personne seule (valable 48 h) ; une fois connecté avec son propre compte, **archiver le compte `installation`**.
7. Le lendemain matin, Admin > Serveur > « Dernières tâches exécutées » : `backup:run` doit être en OK et une archive
   doit apparaître. **En cas d'échec** (outil `mysqldump` absent ou bloqué sur l'hébergement) : le signaler, la
   sauvegarde de la base devra passer par une autre voie ; en attendant, OVH conserve ses propres sauvegardes de
   l'espace FTP et de la base (restaurables depuis l'espace client).

La suite du test (formateurs, import GESCOF, recette) est celle de la procédure de mise en service de test ; les
liens d'accès se génèrent tous par le bouton « Lien d'accès ».

## 4. Fichiers à ne jamais écraser ni supprimer sur le serveur

`extranet/.env`, `extranet/.htpasswd`, `extranet/storage/app/` (documents, ressources, fiches, photos, sauvegardes),
`extranet/storage/logs/`. Le paquet contient des dossiers `storage/` vides : FileZilla les fusionne sans rien
supprimer, mais ne jamais utiliser une synchronisation « miroir » qui effacerait les fichiers absents du paquet.

## 5. Mettre à jour

1. Préparer le paquet de la nouvelle version (`scripts/paquet-ovh.sh <tag> --htpasswd …`, même chemin).
2. **Maintenance** : déposer `extranet/storage/framework/down` contenant (choisir un mot secret) :
   ```json
   {"secret": "mot-secret-du-jour", "retry": 60, "status": 503}
   ```
   Les visiteurs voient la page « maintenance » ; ouvrir `https://…/mot-secret-du-jour` donne l'accès à l'équipe
   (cookie de contournement).
3. Déposer le contenu du paquet dans `extranet/` (en écrasant, sans toucher au § 4).
4. Admin > Serveur > **Mettre à jour la base** si des mises à jour sont listées (sauvegarde préalable cochée).
5. Supprimer `storage/framework/down`. Vérifier la page Serveur.

Les fichiers retirés d'une version restent sur le serveur (sans effet) ; un nettoyage complet se fait en déposant le
paquet dans un dossier neuf puis en échangeant les dossiers, en y recopiant le § 4.

## 6. Ouverture réelle (fin du test)

Service d'e-mail (Brevo envisagé, sinon SMTP OVH) dans les `MAIL_*` du `.env`, retrait de `--htpasswd` (nouveau
paquet ou `public/.htaccess` d'un paquet sans protection), base repartie de zéro ou données de test supprimées,
puis `EDL_ENVOIS_STAGIAIRES=true`. Page Serveur sans point bloquant.
