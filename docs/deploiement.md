# Déploiement — Extranet EDL+

Ce document décrit comment installer l'extranet sur un serveur, le mettre à jour et le contrôler.
Il complète [`sauvegardes.md`](sauvegardes.md) (sauvegardes et restauration) et le modèle de
configuration [`.env.production.example`](../.env.production.example).

## 1. Ce qu'il faut sur le serveur

| Élément | Exigence |
|---|---|
| PHP | **8.4 ou plus** (le code utilise des fonctions de 8.4) |
| Extensions PHP | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` + **OPcache** activé |
| Base de données | MySQL 8 ou MariaDB 10.6+, avec l'outil `mysqldump` (sauvegardes) |
| Outils | Composer 2 ; Node 20+ seulement si les assets sont compilés sur le serveur |
| Réseau | **HTTPS** obligatoire (certificat valide) ; envoi d'e-mails (SMTP) ; accès sortant vers le stockage hors serveur des sauvegardes |
| Tâches planifiées | un cron toutes les minutes (voir §4) |
| Accès | SSH par clé, pour l'exploitation |

Réglages `php.ini` à vérifier (le contrôle `edl:preflight` les teste) :

```
upload_max_filesize = 64M      ; ≥ EDL_UPLOAD_MAX_KO (50 Mo par défaut)
post_max_size       = 128M     ; plusieurs fichiers peuvent être déposés d'un coup
memory_limit        = 256M
max_execution_time  = 120
opcache.enable      = 1
```

**La racine web du site doit être le dossier `public/`.** Rien d'autre ne doit être accessible depuis
internet (`.env`, `storage/`, `vendor/`, `.git/`).

## 2. Première installation

```sh
git clone https://github.com/FaeNoRi/extranet-edl.git && cd extranet-edl
git checkout --detach <tag-de-version>

composer install --no-dev --optimize-autoloader
npm ci && npm run build            # ou déposer public/build compilé ailleurs (serveur sans Node)

cp .env.production.example .env    # puis remplir chaque valeur <…> ; chmod 600 .env
php artisan key:generate --force   # à conserver : voir sauvegardes.md

php artisan migrate --force
php artisan db:seed --force        # en production : charge SEULEMENT le référentiel pédagogique
php artisan storage:link

php artisan edl:creer-admin <identifiant> <email> <NOM> <Prénom>   # le lien de création du mot de passe part par e-mail
php artisan optimize
php artisan edl:preflight
```

> **Ne jamais exécuter `UserSeeder` ni `DemoSeeder` en production** (faux comptes, mot de passe connu).
> `php artisan db:seed` est protégé : en production il ne charge que le référentiel. `edl:preflight`
> bloque la mise en service si un administrateur a encore le mot de passe de démonstration.

Droits : le serveur web doit pouvoir écrire dans `storage/` et `bootstrap/cache/`.

## 3. Mettre à jour

```sh
scripts/deployer.sh <tag-ou-branche>                 # sauvegarde, maintenance, mise à jour, migrations, caches
scripts/deployer.sh <tag-ou-branche> --sans-assets   # serveur sans Node : déposer d'abord public/build compilé
```

Le script fait une **sauvegarde avant** toute modification. Si une étape échoue, l'extranet reste en
maintenance : corriger puis relancer, ou restaurer la sauvegarde (voir `sauvegardes.md`). Ne pas
utiliser `migrate:rollback` en production : restaurer plutôt la sauvegarde d'avant le déploiement.

## 4. Tâches planifiées (cron)

Une seule ligne, à ajouter pour l'utilisateur qui fait tourner l'application :

```
* * * * * cd /chemin/vers/extranet-edl && php artisan schedule:run >> /dev/null 2>&1
```

Elle déclenche, aux heures fixées dans `routes/console.php` : sauvegarde (2h30), nettoyage des
sauvegardes (2h50), signalement des purges de comptes (3h), purge des imports GESCOF non appliqués
(3h15), purge du journal des actions (3h30), contrôle des sauvegardes (8h).

**Sans ce cron, rien de tout cela ne tourne, sans aucune erreur visible.** Un témoin de vie est
enregistré chaque minute ; `edl:preflight` signale s'il est absent.

## 5. Contrôles après déploiement

```sh
php artisan edl:preflight
```

Lit la configuration et signale : **bloquant** (ne pas ouvrir) ou **à traiter** (à régler vite). Il
vérifie notamment : mode production et debug coupé, HTTPS, cookie de session sécurisé, migrations,
absence de compte de démonstration, envoi d'e-mails réel, droits d'écriture, chiffrement des
sauvegardes, `mysqldump`, sauvegarde hors serveur, cron, informations légales, OPcache, limites de
téléversement.

Parcours à faire à la main (10 minutes) :

1. `https://…/login` s'affiche sans avertissement de certificat, `http://` redirige vers `https://` (redirection à configurer chez l'hébergeur).
2. L'administrateur reçoit son lien, crée son mot de passe, se connecte.
3. Créer un formateur (avec envoi d'accès) : l'e-mail arrive, le lien fonctionne.
4. Simuler (sans appliquer) un import GESCOF.
5. Déposer un document de structure ; l'ouvrir avec un compte stagiaire OP de test (visionneuse, filigrane).
6. `php artisan backup:run` puis `backup:list` : l'archive existe (sur le serveur **et** hors serveur).
7. Vérifier `/mentions-legales` et `/politique-de-confidentialite` (référent RGPD, n° d'activité).

## 6. Exploitation courante

- **Mises à jour de sécurité** : Dependabot propose chaque semaine les mises à jour ; la CI refuse
  les dépendances avec une faille connue (`composer audit`, `npm audit`). Les relire et déployer.
- **Journaux** : `storage/logs/laravel-AAAA-MM-JJ.log`, conservés 14 jours. Ils peuvent contenir des
  données personnelles : ne pas les copier ni les envoyer par e-mail.
- **Comptes** : administrateurs nominatifs (pas de compte partagé) ; supprimer ceux qui n'en ont
  plus besoin ; mots de passe d'au moins 10 caractères (règle de l'application).
- **Test de restauration** : une fois par trimestre (voir `sauvegardes.md`).
- **Incident de sécurité** : couper l'accès (`php artisan down`), conserver les journaux, changer
  `APP_KEY` si le `.env` est exposé (déconnecte tout le monde) et les mots de passe SMTP/base, prévenir
  le référent RGPD (obligation de notification à la CNIL sous 72 h en cas de violation de données).

## 7. Ouverture au test interne

Avant de donner l'accès à l'EDL : `edl:preflight` sans point bloquant, sauvegarde restaurée une fois
sur ce serveur, e-mails reçus, fichier d'import de test supprimé. Le test interne se fait de
préférence avec une ou deux sessions réelles (une OP, une FPC) avant tout import général.
