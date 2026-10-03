# AGCA interclubs

Application PHP 8 / MySQL 8 des championnats interclubs AGCA (Mixte 2e série, Homme 1re série).

## Installation locale
En une commande sur macOS : `sh bin/installer-local.sh` (voir `docs/installation-locale.md`). À la main :
1. `brew install php composer mysql && brew services start mysql`
2. `composer install`
3. `cp config/config.php.dist config/config.php` puis renseigner la base.
4. `php bin/migrate.php` (schéma) ; puis, pour charger la saison 2026-27, charger d'abord le dump dans la base (`mysql -u root agca_dev < _DATABASEUU_.sql`), puis lancer `php bin/import.php` (argument optionnel : chemin du config.php) — ou `php bin/demo.php` pour un jeu de démonstration.
5. `composer serve` puis http://localhost:8080

## Commandes
- `composer test` : tests PHPUnit.
- `php bin/relances.php` : relances 48 h (cron quotidien en production).

## Déploiement
Voir `docs/bascule.md`.

## Démonstration
`php bin/demo.php` sur une base vide crée une saison DEMO, ADMIN/admin1234 et des capitaines (mot de passe demo1234), puis le contenu initial du site et deux actualités de démonstration.

## Contenu du site
- Espace admin (`/admin/contenu`, réservé à `est_admin`) : pages, actualités, compétitions et leur palmarès, golfs, organigramme, albums photo, documents — chacun avec sa propre liste et son formulaire d'édition.
- Texte enrichi : les champs `corps_md` sont saisis en Markdown réduit (titres, gras/italique, listes, liens) et rendus par `Agca\Domain\Markdown` dans la colonne `corps_html` correspondante à l'enregistrement ; c'est cette colonne qui est affichée, jamais le Markdown brut.
- Documents : types autorisés `pdf, doc, docx, xls, xlsx, jpg, jpeg, png`, 10 Mo maximum, stockés dans `public/documents/` (dossier git-ignoré, à sauvegarder en production — voir `docs/bascule.md`) sous un nom régénéré ; un document peut être joint à une page (ex. Statuts) ou à un palmarès de compétition.
- `php bin/contenu-initial.php [config/config.php]` : importe une fois le contenu initial (pages, compétitions et palmarès, golfs, albums) repris de l'ancien site depuis `db/contenu/initial.php` ; sans effet si le contenu existe déjà (idempotent).

## Structure
- `src/Domain` : règles sportives pures (grille, calcul de rencontre, classement, contrôles, score, Markdown).
- `src/App` : HTTP, session, dépôts, services (saisie, forfait, saison, notifications, relances, contenu du site).
- `templates` : vues. `db/migrations` : schéma. `bin` : commandes. `docs` : cahier des charges, conception, bascule.

## Sauvegarde et restauration
L'application ne modifie jamais les tables héritées de l'ancien site (seul `bin/import.php` les lit, en lecture seule) : une sauvegarde de la base avant la bascule protège donc aussi bien les anciennes données que les nouvelles tables `agca_*`. `Migrations::reinitialiser()` (qui vide les tables `agca_*`) n'est utilisée que par la suite de tests (`tests/App/DbTestCase.php`) et ne doit jamais être appelée en production. Le dossier `public/documents/` (documents téléversés depuis l'admin) est git-ignoré : il doit être sauvegardé séparément, au même rythme que la base.
