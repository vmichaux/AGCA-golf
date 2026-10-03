#!/bin/sh
# Installation locale en une commande (macOS, Homebrew).
# Usage : sh bin/installer-local.sh [chemin/vers/ancienne-base.sql]
#   sans argument      : saison de démonstration (ADMIN/admin1234, capitaines demo1234)
#   avec le dump SQL   : saison réelle 2026-27 importée depuis l'ancienne base
set -eu
cd "$(dirname "$0")/.."
DUMP="${1:-}"

echo "== 1/5 Outils (PHP, Composer, MySQL)"
if ! command -v brew >/dev/null 2>&1; then
  echo "Homebrew manquant. Installez-le d'abord : https://brew.sh (copier la commande affichée dans le Terminal), puis relancez ce script."; exit 1
fi
for outil in php composer mysql; do
  command -v "$outil" >/dev/null 2>&1 || brew install "$outil"
done
brew services start mysql >/dev/null 2>&1 || true
i=0; until mysqladmin ping >/dev/null 2>&1 || [ $i -ge 30 ]; do i=$((i+1)); sleep 1; done
mysqladmin ping >/dev/null 2>&1 || { echo "MySQL ne démarre pas. Essayez : brew services restart mysql"; exit 1; }

echo "== 2/5 Dépendances PHP"
composer install --quiet

echo "== 3/5 Configuration et base agca_dev"
if [ ! -f config/config.php ]; then
  sed -e "s|'debug'    => false, // passer à true en local|'debug'    => true,|" \
      -e "s|'https'    => true,  // false uniquement en local http|'https'    => false,|" \
      config/config.php.dist > config/config.php
fi
mysql -u root -e "CREATE DATABASE IF NOT EXISTS agca_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mkdir -p public/documents

echo "== 4/5 Tables et données"
php bin/migrate.php
if [ -n "$DUMP" ]; then
  mysql -u root agca_dev < "$DUMP"
  php bin/import.php
  php bin/contenu-initial.php
  echo "Saison 2026-27 importée : identifiants et mots de passe des capitaines inchangés."
elif ! mysql -u root agca_dev -N -e "SELECT COUNT(*) FROM agca_saison" | grep -qv '^0$'; then
  php bin/demo.php
  echo "Saison de démonstration : ADMIN / admin1234, capitaines (SALON, FREGATE, ORANGE, DIGNE…) / demo1234."
else
  echo "La base contient déjà une saison : données conservées."
fi

echo "== 5/5 Lancement"
echo "Ouvrez http://localhost:8080 dans votre navigateur. Pour arrêter : Ctrl+C."
exec php -S localhost:8080 -t public public/index.php
