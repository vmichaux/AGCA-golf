#!/bin/sh
# Construit l'archive déployable sur IONOS (préproduction ou production) :
# fichiers de l'application sans outils de développement, tests, docs ni config locale.
# Usage : sh bin/paquet.sh [chemin/de/l/archive.zip]   (défaut : agca-paquet.zip à la racine)
set -eu
cd "$(dirname "$0")/.."
DEST="${1:-$PWD/agca-paquet.zip}"
composer install --no-dev --optimize-autoloader --quiet
rm -f "$DEST"
zip -qr "$DEST" .htaccess README.md composer.json composer.lock bin config/config.php.dist db public src templates vendor \
  -x 'public/maquette/*' 'public/documents/*' '*.DS_Store'
zip -q "$DEST" public/documents/.htaccess
composer install --quiet
echo "Archive : $DEST ($(du -h "$DEST" | cut -f1))"
