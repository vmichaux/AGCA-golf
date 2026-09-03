# Bascule en production — semaine du 15 septembre 2026

## Pré-requis IONOS (panneau client)
1. PHP : version 8.2 ou plus récente pour le domaine agca-amitie.org.
2. MySQL : base existante (même serveur), vérifier la version 8 ; noter hôte, nom de base, utilisateur, mot de passe.
3. Boîte e-mail `noreply@agca-amitie.org` créée ; noter le mot de passe et le serveur SMTP (smtp.ionos.fr, port 587 STARTTLS ou 465 SSL). Tant qu'elle n'existe pas : `mail.enabled = false` (les e-mails sont journalisés, rien n'est envoyé).
4. Tâche cron quotidienne (IONOS « Tâches cron ») : `/usr/bin/php8.2 /agca-app/bin/relances.php` à 07:00 (chemin absolu du binaire PHP, requis par IONOS).
5. Sauvegarde complète de la base actuelle (export phpMyAdmin) avant toute opération.

## Fichiers
1. En local : `composer test` vert, puis `composer install --no-dev --optimize-autoloader` (vendor/ sans les outils de test, prêt pour la copie), déposer les fichiers (étape 2), puis `composer install` pour rétablir les outils de développement.
2. Déposer le dépôt complet (sauf `config/config.php`, `.git`, `tests/`) dans un dossier hors racine web, par exemple `/agca-app/`.
3. Créer `/agca-app/config/config.php` à partir de `config.php.dist` avec les accès MySQL, SMTP, `app.base_url = https://www.agca-amitie.org`, `app.debug = false`, un `app.secret` aléatoire.
4. Racine web du domaine : pointer `agca-amitie.org` vers `/agca-app/public` (IONOS → Domaines → répertoire cible) — c'est la configuration recommandée. Si ce n'est pas possible, déposer le `.htaccess` de la racine du dépôt à la racine web : il redirige tout vers `public/` ; ce repli est provisoire (les dossiers `config/`, `src/`, `db/`, `templates/`, `tests/`, `vendor/` et `bin/` portent alors chacun un `.htaccess Require all denied`, à ne pas retirer tant que la racine web n'est pas basculée sur `public/`).
5. L'application doit être servie à la racine du domaine (`https://www.agca-amitie.org/`), les URL générées (`app.base_url`, liens dans les e-mails) sont absolues et supposent ce point d'entrée.
6. Les anciennes pages (`index.html`, `*.php`, images, photos) restent en place dans l'ancien dossier ; elles ne sont plus liées mais restent accessibles par URL directe le temps de la phase 2.

## Base de données
1. `php bin/migrate.php /agca-app/config/config.php` → crée les tables `agca_*` à côté des anciennes (aucune table ancienne modifiée).
2. `php bin/import.php /agca-app/config/config.php` → équipes, identifiants (mots de passe inchangés), saison 2026-27, divisions et calendrier. Lire le rapport ; aucune ligne « ERREUR » attendue.
3. Vérifier `https://www.agca-amitie.org/serie/M/suivi` contre l'ancienne page `M_suivi_page.php` : mêmes appariements, mêmes dates.
4. Point à confirmer avec l'admin : l'ancienne page affichait pour ROQUEBRUNE – CHATEAU-L'ARC (aller J2) « date report 7/11/26, date match 14/11/26 ». Si le match est bien déplacé au 7 novembre, l'admin modifie la date sur la feuille (Actions administrateur) ; sinon rien à faire.

## Tests avant ouverture
1. Connexion ADMIN avec le mot de passe actuel : le hachage passe en bcrypt à la première connexion.
2. Connexion d'un capitaine volontaire avec son identifiant habituel.
3. Ne pas créer de rencontre de test en production : la saisie a été validée en local avec `bin/demo.php` ; en production, seule la connexion est testée avant ouverture.
4. `mail.enabled = true` une fois la boîte créée : envoyer un test en changeant la date d'une rencontre puis en la remettant (deux e-mails de report au capitaine invité et à l'admin).

## Ouverture
1. Remplacer `index.html` de l'ancien site par une redirection vers la nouvelle racine si les deux cohabitent, ou supprimer l'ancien `index.html` si la racine web a été déplacée.
2. E-mail à tous les capitaines : nouvelle adresse de connexion `https://www.agca-amitie.org/connexion`, identifiant inchangé (nom d'équipe + série), mot de passe inchangé, marche à suivre pour la saisie (3 lignes), contact admin en cas d'oubli.
3. Surveiller le journal (`/admin`) après la 1re journée du 26 septembre.

## Retour arrière
Les anciennes pages et tables sont intactes : repointer la racine web vers l'ancien dossier suffit.
