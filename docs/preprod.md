# Préproduction sur IONOS — test.agca-amitie.org

Copie du site sur l'hébergement IONOS, sur un sous-domaine, avec sa propre base et protégée par un mot de passe,
pour que Robert Michaux puisse tester avant la bascule. Même archive et mêmes scripts qu'en production
(`docs/bascule.md`) : la préproduction sert de répétition générale.

## 1. Panneau IONOS (Victoria)

1. **PHP** : Hébergement → Version PHP → 8.2 ou plus récente pour le domaine. Noter la version exacte
   (le binaire en SSH s'appelle `/usr/bin/php8.2`, `/usr/bin/php8.3`…).
2. **Base MySQL dédiée** : Hébergement → Bases de données → Créer une base (description « agca preprod »).
   Noter : hôte (`db5xxxxxxx.hosting-data.io`), nom de base, utilisateur, mot de passe.
3. **Ancienne base dans la nouvelle** : ouvrir phpMyAdmin sur cette base → Importer → fichier
   `_DATABASEUU_.sql` (le dump de l'ancien site). Les scripts d'import lisent les anciennes tables
   (`Mequipe`, `Midentifiant`…) pour créer équipes, identifiants et calendrier.
4. **Sous-domaine** : Domaines & SSL → agca-amitie.org → Sous-domaines → créer `test.agca-amitie.org`,
   répertoire cible `/agca-preprod/public` (créer le dossier `agca-preprod` d'abord si le panneau l'exige).
   Activer le certificat SSL sur ce sous-domaine (Domaines & SSL → SSL). Sans SSL, voir « Cas particuliers ».
5. **Accès SFTP et SSH** : Hébergement → Accès SFTP et SSH → noter hôte (`access-xxxxxxxxx.webspace-host.com`),
   utilisateur et mot de passe ; vérifier que SSH est activé (il l'est sur les offres Hébergement Web IONOS).

## 2. Dépôt des fichiers

Sur le Mac, construire l'archive (application seule, sans tests ni outils de développement, 1,2 Mo) :

```bash
sh bin/paquet.sh
```

Déposer `agca-paquet.zip` à la racine de l'espace web (SFTP : FileZilla, Cyberduck ou l'explorateur de
fichiers IONOS). Puis en SSH :

```bash
ssh UTILISATEUR@HOTE
```

```bash
mkdir -p agca-preprod && cd agca-preprod && unzip -q ../agca-paquet.zip && rm ../agca-paquet.zip && mkdir -p public/documents && chmod 775 public/documents && cp config/config.php.dist config/config.php && pwd
```

Noter le chemin absolu affiché par `pwd` (utile pour la protection par mot de passe).

Éditer `config/config.php` (`nano config/config.php`) :

| Clé | Valeur préproduction |
|---|---|
| `db.dsn` | `mysql:host=db5xxxxxxx.hosting-data.io;dbname=NOM_BASE;charset=utf8mb4` |
| `db.user` / `db.pass` | utilisateur et mot de passe de la base |
| `mail.enabled` | `false` (aucun e-mail envoyé, tout est journalisé dans l'admin) |
| `app.base_url` | `https://test.agca-amitie.org` |
| `app.secret` | chaîne aléatoire : `php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'` |
| `app.debug` | `false` |
| `app.https` | `true` |

## 3. Base de données

Toujours en SSH, dans `agca-preprod` (remplacer `php8.2` par la version notée en 1.1) :

```bash
php8.2 bin/migrate.php && php8.2 bin/import.php && php8.2 bin/contenu-initial.php
```

Lire le rapport de l'import : aucune ligne « ERREUR » attendue. Les scripts ne touchent pas aux anciennes tables.

## 4. Protection par mot de passe et exclusion des moteurs de recherche

Créer le fichier des mots de passe (choisir un identifiant et un mot de passe simples à communiquer à Robert) :

```bash
php8.2 -r 'echo "agca:" . password_hash("MOT_DE_PASSE", PASSWORD_BCRYPT) . PHP_EOL;' > .htpasswd
```

Ajouter en tête de `public/.htaccess` (chemin = celui affiché par `pwd` à l'étape 2) :

```apache
AuthType Basic
AuthName "AGCA preproduction"
AuthUserFile /CHEMIN/ABSOLU/agca-preprod/.htpasswd
Require valid-user
Header set X-Robots-Tag "noindex, nofollow"
```

Et créer `public/robots.txt` :

```
User-agent: *
Disallow: /
```

## 5. Vérification

1. `https://test.agca-amitie.org/` demande le mot de passe puis affiche l'accueil.
2. `/serie/M/suivi` et `/serie/H1/suivi` : mêmes appariements et dates que l'ancien site (`M_suivi_page.php`).
3. Connexion ADMIN avec le mot de passe actuel de Robert (identifiants et mots de passe inchangés).
   Le hachage passe en bcrypt à la première connexion, dans la base de préproduction seulement.
4. Les albums photo pointent vers `https://www.agca-amitie.org/images…` : les photos de l'ancien site s'affichent.

Communiquer à Robert : l'adresse, l'identifiant et le mot de passe de la protection, et lui rappeler que
ses identifiants habituels fonctionnent. Il peut saisir des feuilles de test sans conséquence :
la base de préproduction sera jetée à la bascule.

## Cas particuliers

- **Pas de SSL sur le sous-domaine** : ne pas tester avec les vrais mots de passe en HTTP. Soit activer
  un certificat pour le sous-domaine (option IONOS), soit installer la préproduction dans un sous-dossier
  du domaine principal, ce qui demande une adaptation de l'application (URL de base avec préfixe).
- **Pas de SSH** : l'archive peut être dézippée en local et déposée dossier par dossier en SFTP
  (288 fichiers) ; les scripts `bin/*.php` doivent alors être lancés autrement — à traiter le moment venu.
- **Remise à zéro** : supprimer les tables `agca_*` dans phpMyAdmin, relancer l'étape 3.
- **Mise à jour du code** : reconstruire l'archive, la déposer, puis en SSH dans `agca-preprod` :
  `unzip -qo ../agca-paquet.zip && rm ../agca-paquet.zip` (la config et les documents sont conservés),
  puis `php8.2 bin/migrate.php` s'il y a de nouvelles migrations.
- **Bascule en production** : même archive, procédure `docs/bascule.md`, racine web du domaine
  principal pointée sur `/agca-app/public`, base de production, `mail.enabled = true`, tâche cron.
