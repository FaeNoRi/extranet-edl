#!/usr/bin/env bash
# Prépare, sur le poste, le dossier à déposer par FTP sur l'hébergement mutualisé OVH (sans SSH,
# donc sans Composer ni Node sur le serveur). Procédure complète : docs/deploiement-ovh.md.
#
#   scripts/paquet-ovh.sh <tag-ou-branche>
#   scripts/paquet-ovh.sh <tag-ou-branche> --htpasswd /homez.NNN/identifiant/extranet/.htpasswd
#
# --htpasswd : protège tout le site par un mot de passe commun (Basic Auth, phase de test) ;
#              chemin ABSOLU du fichier .htpasswd sur le serveur.
#
# Sous Windows (Git Bash, PHP par défaut en 8.2) :
#   PHP="C:/laragon/bin/php/php-8.4.7-nts-Win32-vs17-x64/php.exe" \
#   COMPOSER_CMD="$PHP C:/ProgramData/ComposerSetup/bin/composer.phar" scripts/paquet-ovh.sh v1.0.0
set -euo pipefail

VERSION="${1:?Indiquer le tag ou la branche à empaqueter (ex. v1.0.0)}"
HTPASSWD=""
if [ "${2:-}" = "--htpasswd" ]; then
    HTPASSWD="${3:?Indiquer le chemin absolu du fichier .htpasswd sur le serveur}"
fi
PHP="${PHP:-php}"
COMPOSER_CMD="${COMPOSER_CMD:-composer}"

cd "$(dirname "$0")/.."

DEST="paquets/extranet-${VERSION//\//-}"
echo "==> Extraction de $VERSION dans $DEST"
rm -rf "$DEST"
mkdir -p "$DEST"
git archive "$VERSION" | tar -x -C "$DEST"

cd "$DEST"

echo "==> Dépendances PHP (sans les outils de développement)"
$COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction

echo "==> Compilation des assets"
npm ci --no-audit --no-fund
npm run build

echo "==> Retrait des fichiers inutiles sur le serveur"
rm -rf node_modules tests .github scripts docs phpunit.xml pint.json .editorconfig .npmrc \
       package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js \
       .env.example AGENTS.md CLAUDE.md README.md

# PHP 8.4 pour le site (sur OVH, la version se règle par .ovhconfig, à la racine du dossier du site
# et du dossier public : voir docs/deploiement-ovh.md avant de déposer ces fichiers).
cat > .ovhconfig <<'OVH'
app.engine=php
app.engine.version=8.4
http.firewall=none
environment=production
container.image=stable64
OVH
cp .ovhconfig public/.ovhconfig

# Redirection http → https, sans boucle derrière le proxy d'OVH (qui peut terminer le TLS).
ENTETE='# Hébergement OVH : redirection vers https (ajouté par scripts/paquet-ovh.sh).
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteCond %{HTTP:X-Forwarded-Proto} !https
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
'
if [ -n "$HTPASSWD" ]; then
    ENTETE="$ENTETE
# Phase de test : accès réservé (mot de passe commun, en plus des comptes de l'extranet).
AuthType Basic
AuthName \"Extranet EDL+ (test)\"
AuthUserFile $HTPASSWD
Require valid-user
"
fi
printf '%s\n%s' "$ENTETE" "$(cat public/.htaccess)" > public/.htaccess

cd - > /dev/null
echo
echo "Paquet prêt : $DEST"
echo "À déposer par FTP dans le dossier « extranet » (sans écraser .env ni storage/app sur le serveur)."
