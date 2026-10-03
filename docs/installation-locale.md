# Faire tourner le site sur son ordinateur

Pour Robert : comment récupérer le projet et le lancer sur un Mac, afin de tester ou de modifier le site sans toucher à Internet.

## Ce qu'il faut, une seule fois

1. **Un compte GitHub** (gratuit) sur https://github.com, et une invitation de Victoria sur le dépôt `vmichaux/AGCA-golf`. Accepter l'invitation reçue par e-mail.
2. **GitHub Desktop** (gratuit) : https://desktop.github.com. L'installer, se connecter avec son compte GitHub.
3. **Homebrew**, l'installeur de logiciels pour Mac : ouvrir l'application Terminal, coller la commande affichée sur https://brew.sh, taper Entrée, saisir le mot de passe de session Mac quand il est demandé. Compter 5 à 10 minutes.

## Récupérer le projet

Dans GitHub Desktop : menu **File → Clone Repository**, choisir `vmichaux/AGCA-golf`, garder l'emplacement proposé (dossier `Documents/GitHub/AGCA-golf`), cliquer **Clone**.

## Lancer le site

Dans GitHub Desktop, menu **Repository → Open in Terminal**, puis coller :

```bash
sh bin/installer-local.sh
```

La première fois, le script installe PHP et MySQL (quelques minutes), prépare la base avec une saison de démonstration, puis lance le site. Ensuite, ouvrir http://localhost:8080 dans le navigateur.

- Administration : identifiant `ADMIN`, mot de passe `admin1234`.
- Capitaines de démonstration : `SALON`, `FREGATE`, `ORANGE`, `DIGNE` (Mixte), mot de passe `demo1234`.

Pour arrêter le site : dans le Terminal, touche Ctrl+C. Pour le relancer un autre jour : la même commande, elle va directement au lancement.

Pour travailler avec la vraie saison 2026-27 à la place de la démonstration, donner au script le fichier d'export de l'ancienne base (celui transmis à Victoria), avant la première installation :

```bash
sh bin/installer-local.sh ~/Desktop/_DATABASEUU_.sql
```

## Modifier et partager ses modifications

Les textes du site se modifient dans l'espace d'administration (menu Admin → Contenu du site), sans toucher au code.

Pour une modification dans le code : éditer les fichiers, vérifier le résultat sur http://localhost:8080, puis dans GitHub Desktop écrire une courte description en bas à gauche, cliquer **Commit to main**, puis **Push origin**. Victoria voit la modification sur GitHub. Dans l'autre sens, **Fetch origin** puis **Pull** récupère ce que Victoria a changé.

## Si quelque chose ne va pas

- « MySQL ne démarre pas » : dans le Terminal, `brew services restart mysql`, puis relancer le script.
- Page blanche ou erreur : copier le message et l'envoyer à Victoria.
- Repartir de zéro : `mysql -u root -e "DROP DATABASE agca_dev"`, puis relancer le script.

Sur Windows, le script ne s'applique pas : installer Laragon (https://laragon.org, inclut PHP, MySQL et Composer), cloner le projet dans `C:\laragon\www`, puis suivre le README (étapes 2 à 5) depuis le terminal de Laragon.
