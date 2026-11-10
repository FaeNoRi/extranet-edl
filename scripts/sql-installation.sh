#!/usr/bin/env bash
# Génère, sur le poste, le script SQL de PREMIÈRE installation de la base OVH (sans SSH, `migrate`
# ne peut pas tourner sur le serveur) : toutes les tables, le référentiel pédagogique et un compte
# administrateur d'installation. À importer UNE SEULE FOIS dans phpMyAdmin, dans une base vide :
# le script supprime et recrée les tables. Les mises à jour suivantes passent par la page Serveur.
#
#   scripts/sql-installation.sh <e-mail du compte d'installation>
#
# Nécessite le MySQL local (Laragon démarré). Le mot de passe du compte « installation » est
# affiché une seule fois dans le terminal : il n'est écrit nulle part ailleurs (seul son hachage
# est dans le fichier SQL). Sous Windows (Git Bash) :
#   PHP="C:/laragon/bin/php/php-8.4.7-nts-Win32-vs17-x64/php.exe" \
#   MYSQL_BIN="C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin" scripts/sql-installation.sh moi@exemple.fr
set -euo pipefail

EMAIL="${1:?Indiquer l adresse e-mail du compte administrateur d installation}"
export EMAIL_INSTALLATION="$EMAIL"
PHP="${PHP:-php}"
MYSQL_BIN="${MYSQL_BIN:+$MYSQL_BIN/}"
BASE="edl_installation_tmp"
MYSQL_USER="${MYSQL_USER:-root}"
export MYSQL_PWD="${MYSQL_PASSWORD:-}"

cd "$(dirname "$0")/.."
mkdir -p paquets
SORTIE="paquets/extranet-installation-$(date +%Y%m%d).sql"

echo "==> Base temporaire $BASE"
"${MYSQL_BIN}mysql" -u "$MYSQL_USER" -e "DROP DATABASE IF EXISTS $BASE; CREATE DATABASE $BASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Mêmes réglages qu'en production (le seeder ne charge alors que le référentiel), sur la base temporaire.
export APP_ENV=production DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE="$BASE" \
       DB_USERNAME="$MYSQL_USER" DB_PASSWORD="$MYSQL_PWD" CACHE_STORE=array SESSION_DRIVER=array MAIL_MAILER=array

echo "==> Tables et référentiel"
"$PHP" artisan migrate --force --seed --no-interaction

echo "==> Compte administrateur d'installation"
MOT_DE_PASSE=$("$PHP" artisan tinker --execute='
    $motDePasse = Illuminate\Support\Str::password(20, symbols: false);
    App\Models\User::create([
        "login" => "installation", "email" => getenv("EMAIL_INSTALLATION"), "nom" => "INSTALLATION",
        "prenom" => "Compte", "role" => App\Enums\Role::Admin, "password" => $motDePasse,
    ]);
    echo $motDePasse;' 2>/dev/null | tail -n 1)

if [ "$("${MYSQL_BIN}mysql" -u "$MYSQL_USER" -N "$BASE" -e "SELECT COUNT(*) FROM users WHERE login = 'installation' AND role = 'admin';")" != "1" ]; then
    echo "Création du compte d'installation impossible." >&2
    exit 1
fi

# Journal vidé : il ne contiendrait que la création des lignes ci-dessus.
"${MYSQL_BIN}mysql" -u "$MYSQL_USER" "$BASE" -e "TRUNCATE activity_log;"

echo "==> Export"
{
    echo "-- Extranet EDL+ : installation de la base ($(git describe --tags --always)), généré le $(date '+%d/%m/%Y %H:%M')."
    echo "-- À importer UNE SEULE FOIS dans une base VIDE (phpMyAdmin > Importer) : supprime et recrée les tables."
    "${MYSQL_BIN}mysqldump" -u "$MYSQL_USER" --no-tablespaces --skip-comments --set-gtid-purged=OFF \
        --default-character-set=utf8mb4 --single-transaction "$BASE"
} > "$SORTIE"

"${MYSQL_BIN}mysql" -u "$MYSQL_USER" -e "DROP DATABASE $BASE;"

echo
echo "Script SQL : $SORTIE"
echo "Compte d'installation : identifiant « installation », mot de passe : $MOT_DE_PASSE"
echo "Notez-le maintenant (il n'est affiché qu'ici), créez les comptes nominatifs puis archivez ce compte."
