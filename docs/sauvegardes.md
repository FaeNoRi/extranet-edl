# Sauvegardes et restauration — Extranet EDL+

Outil : [spatie/laravel-backup](https://spatie.be/docs/laravel-backup) (configuration : `config/backup.php`).

## Ce qui est sauvegardé

| Élément | Sauvegardé | Pourquoi |
|---|---|---|
| Base de données (MySQL, dump complet) | oui | comptes, sessions, séances, journal, questionnaires… |
| `storage/app/private` | oui | documents, ressources pédagogiques, fiches pédagogiques PDF |
| `storage/app/public` | oui | photos des formateurs |
| `storage/app/private/gescof` | **non** | fichiers d'import temporaires (purgés sous 7 jours) |
| `.env` | **non** | contient des secrets : à conserver à part (voir ci-dessous) |
| code (`app/`, `vendor/`…) | non | se redéploie depuis Git (`composer install`, `npm run build`) |

L'archive (`.zip`) est **chiffrée en AES-256** avec `BACKUP_ARCHIVE_PASSWORD` : elle contient des données
personnelles, y compris celles de mineurs.

## Fonctionnement

Planifié dans `routes/console.php` (le planificateur Laravel doit tourner : cron `* * * * * php artisan schedule:run`) :

| Heure | Commande | Rôle |
|---|---|---|
| 02:30 | `backup:run` | crée l'archive |
| 02:50 | `backup:clean` | supprime les archives trop anciennes |
| 08:00 | `backup:monitor` | alerte si la dernière sauvegarde a plus d'1 jour ou si le stockage dépasse 5 Go |

- **Conservation : 28 jours** (7 jours de toutes les archives, puis une par jour pendant 21 jours).
  Elle est volontairement courte : une archive contient aussi les comptes déjà purgés, qui n'y disparaissent
  qu'à son expiration. Durée à valider avec l'EDL (registre des traitements) ; la politique de confidentialité
  l'annonce.
- **Alertes e-mail** (échec de sauvegarde / sauvegarde périmée / échec du nettoyage) : `BACKUP_NOTIFICATION_EMAIL`
  (défaut : `EDL_EMAIL`). Pas de message en cas de succès.
- **Stockage** : disque `backups` = `storage/app/backups/extranet-edl/AAAA-MM-JJ-HH-MM-SS.zip`.
  **Une sauvegarde qui reste sur le serveur ne protège pas d'une panne du serveur** : en production, déclarer
  un disque hors-site (SFTP ou S3, à ajouter dans `config/filesystems.php`) et le nommer dans
  `BACKUP_OFFSITE_DISK`. Les archives sont alors copiées sur les deux disques.

## Mise en place (production)

1. Dans le `.env` : `BACKUP_ARCHIVE_PASSWORD` (long, aléatoire), `DB_DUMP_BINARY_PATH` si `mysqldump` n'est pas
   dans le `PATH`, `BACKUP_NOTIFICATION_EMAIL`, `BACKUP_OFFSITE_DISK`. Sous Windows, écrire le chemin avec des
   barres obliques normales : `DB_DUMP_BINARY_PATH="C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin"`.
2. **Mettre le mot de passe d'archive, `APP_KEY` et le `.env` dans un gestionnaire de mots de passe**, à un
   autre endroit que le serveur et que les sauvegardes. Sans le mot de passe d'archive, les sauvegardes sont
   illisibles ; sans `APP_KEY`, les sessions et jetons chiffrés sont perdus (les mots de passe, eux, sont hachés
   et restent valides).
3. Vérifier : `php artisan backup:run`, puis `php artisan backup:list`.

## Restaurer

À faire sur un serveur où le code est déjà déployé (PHP 8.4, `composer install --no-dev -o`, `.env` recréé
depuis le gestionnaire de mots de passe, base MySQL vide créée).

1. **Récupérer l'archive** voulue (disque `backups` ou hors-site).
2. **Extraire** avec le mot de passe d'archive (7-Zip ou WinZip savent ouvrir le chiffrement AES-256 ; le
   `unzip` standard non). On obtient :
   ```
   db-dumps/mysql-edl_plus.sql
   private/…      (documents, ressources, fiches pédagogiques)
   public/…       (photos des formateurs)
   ```
3. **Base de données** (la base cible doit être vide) :
   ```sh
   mysql -u <utilisateur> -p edl_plus < db-dumps/mysql-edl_plus.sql
   ```
4. **Fichiers** : copier `private/` et `public/` dans `storage/app/` (donc `storage/app/private` et
   `storage/app/public`), puis `php artisan storage:link`.
5. **Finaliser** :
   ```sh
   php artisan migrate --force      # si le code est plus récent que l'archive
   php artisan optimize:clear
   php artisan optimize
   ```
6. **Contrôler** : connexion d'un compte admin, ouverture d'une session de formation, d'un document et d'une
   fiche pédagogique PDF.

Si un seul fichier est perdu, inutile de tout restaurer : extraire l'archive ailleurs et recopier le fichier.

### Attention aux données purgées

Restaurer une archive **réintroduit les comptes supprimés depuis**. Après une restauration, rejouer les purges
et les demandes d'effacement reçues depuis la date de l'archive (journal des actions, onglet RGPD).

## Test de restauration (à faire avant la mise en service puis une fois par trimestre)

Une sauvegarde jamais restaurée n'est pas une sauvegarde. Procédure sans risque :

1. Extraire la dernière archive dans un dossier temporaire.
2. Importer `db-dumps/*.sql` dans une base temporaire (`CREATE DATABASE edl_plus_test`).
3. Comparer le nombre de lignes de chaque table avec la base réelle, puis supprimer la base temporaire.

Dernier test : **2026-10-06** (poste de développement, base `edl_plus`) — 30 tables restaurées, aucune
différence de nombre de lignes ; fichiers `private/` et `public/` présents avec leurs chemins relatifs.
