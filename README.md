# AGCA interclubs

Application PHP 8 / MySQL 8 des championnats interclubs AGCA (Mixte 2e série, Homme 1re série).

## Installation locale
1. `brew install php composer mysql && brew services start mysql`
2. `composer install`
3. `cp config/config.php.dist config/config.php` puis renseigner la base.
4. `php bin/migrate.php` (schéma), `php bin/import.php chemin/vers/_DATABASEUU_.sql` (saison 2026-27) ou `php bin/demo.php` (jeu de démonstration).
5. `composer serve` puis http://localhost:8080

## Commandes
- `composer test` : tests PHPUnit.
- `php bin/relances.php` : relances 48 h (cron quotidien en production).

## Déploiement
Voir `docs/bascule.md`.
