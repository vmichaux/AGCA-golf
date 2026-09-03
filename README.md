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

## Démonstration
`php bin/demo.php` sur une base vide crée une saison DEMO, ADMIN/admin1234 et des capitaines (mot de passe demo1234).

## Structure
- `src/Domain` : règles sportives pures (grille, calcul de rencontre, classement, contrôles, score).
- `src/App` : HTTP, session, dépôts, services (saisie, forfait, saison, notifications, relances).
- `templates` : vues. `db/migrations` : schéma. `bin` : commandes. `docs` : cahier des charges, conception, bascule.

## Sauvegarde et restauration
L'application ne modifie jamais les tables héritées de l'ancien site (seul `bin/import.php` les lit, en lecture seule) : une sauvegarde de la base avant la bascule protège donc aussi bien les anciennes données que les nouvelles tables `agca_*`. `Migrations::reinitialiser()` (qui vide les tables `agca_*`) n'est utilisée que par la suite de tests (`tests/App/DbTestCase.php`) et ne doit jamais être appelée en production.
