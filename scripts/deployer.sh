#!/usr/bin/env bash
# Mise à jour de l'extranet sur le serveur — à lancer depuis la racine de l'application.
#
#   scripts/deployer.sh <tag-ou-branche>            # déploie la version demandée
#   scripts/deployer.sh <tag-ou-branche> --sans-assets   # serveur sans Node : public/build est déposé à part
#
# Prérequis et procédure complète : docs/deploiement.md. En cas d'échec, l'extranet reste en
# maintenance : corriger, relancer, ou restaurer (docs/sauvegardes.md).
set -euo pipefail

VERSION="${1:?Indiquer le tag ou la branche à déployer (ex. v1.0.0)}"
SANS_ASSETS="${2:-}"
PHP="${PHP:-php}"
COMPOSER="${COMPOSER:-composer}"

cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Fichier .env absent : voir .env.production.example." >&2; exit 1; }

echo "==> Sauvegarde préalable (base + fichiers)"
$PHP artisan backup:run --disable-notifications

echo "==> Maintenance"
$PHP artisan down --retry=60 || true

echo "==> Récupération de $VERSION"
git fetch --tags --prune origin
git checkout --detach "$VERSION"

echo "==> Dépendances PHP"
$COMPOSER install --no-dev --optimize-autoloader --no-interaction

if [ "$SANS_ASSETS" != "--sans-assets" ]; then
    echo "==> Compilation des assets"
    npm ci
    npm run build
else
    [ -d public/build ] || { echo "public/build absent : déposer les assets compilés (npm run build) avant de continuer." >&2; exit 1; }
fi

echo "==> Base de données"
$PHP artisan migrate --force

echo "==> Liens et caches"
$PHP artisan storage:link || true
$PHP artisan optimize:clear
$PHP artisan optimize

echo "==> Remise en service"
$PHP artisan up

echo "==> Contrôle"
$PHP artisan edl:preflight || echo "Des points bloquants sont signalés ci-dessus." >&2
