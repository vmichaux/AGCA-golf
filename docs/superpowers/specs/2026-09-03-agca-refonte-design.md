# AGCA — Refonte du site interclubs : conception validée

Date : 3 septembre 2026. Statut : validé en dialogue avec Victoria (pour l'admin Robert Michaux).
Complète le cahier des charges v1 du 3 septembre 2026, qui fait foi en cas de doute sur les règles sportives.

## 1. Décisions prises pendant le cadrage

| Sujet | Décision |
|---|---|
| Règlement H1 | Le cahier des charges fait foi (5 joueurs, index libre, 15-0, confrontation directe avant la différence). Le Google Doc « Règlement Série 1 Mixte » 2020 est obsolète. D1 non utilisé en 2026-27. |
| Identifiants capitaines | Repris tels quels : un identifiant par équipe **et par série** (le nom d'équipe seul n'est pas unique : SALON, ORANGE, VALGARDE-1, VICTORIA existent en M et en H1 avec des mots de passe différents). La page de connexion demande donc équipe + série + mot de passe. |
| Poules Mixte 2026-27 | Les cinq poules affichées sur le site actuel, noms conservés tels quels : DIV2/POULE B, DIV3/POULE C, DIV4/POULE D, DIV5/POULE E, DIV à 5 n°1. VALGARDE-2 retirée cette saison. |
| Format des scores | Résultat obligatoire Gagné / Nul / Perdu vu du recevant ; points de match dérivés. Score structuré optionnel (trous d'avance + trous restants, « 1 UP », « AS »), cohérent avec le résultat. |
| Forfait | Score 15-0 littéral dans les deux séries (comme aujourd'hui). 0 point au forfaitaire, 3 au bénéficiaire, +1 si le bénéficiaire était invité (4 au total). |
| Amorçage joueurs | On part de zéro : aucun import des anciens noms de joueurs. |
| Visibilité des feuilles | Détail d'une feuille (noms, index) visible uniquement par les deux capitaines concernés et l'admin. Classements et suivi publics. |
| Stack | Option A : PHP 8 structuré sans framework, MySQL 8, hébergement IONOS mutualisé conservé. |
| Boîte noreply | Pas encore créée. Le code fonctionne sans elle (mode journalisation). |

## 2. Architecture

PHP 8.2+, MySQL 8, aucun framework. Composer uniquement pour PHPMailer et PHPUnit ; le dossier `vendor/` est commité pour déployer par simple copie.

```
public/            seul dossier exposé : index.php (front controller), css/, js/, img/
src/Domain/        métier pur, sans base ni HTTP (voir §4)
src/App/           Router, Controllers, Session/Auth, Db (PDO), Mailer, View
templates/         vues PHP : public/, capitaine/, admin/, mail/
db/migrations/     scripts SQL numérotés + script d'import initial
bin/               commandes CLI : relances (cron), import, création admin
tests/             PHPUnit (domaine + quelques tests d'intégration base)
config/            config.php.dist ; config.php réel hors git (DB, SMTP, secret)
docs/              cahier des charges, cette conception, procédure de bascule
```

Principes : chaque requête passe par `public/index.php` → routeur → contrôleur → vue. Les contrôleurs sont minces ; tout calcul est dans `src/Domain`. Pas d'étape de build front : CSS maison responsive et JavaScript vanilla (auto-complétion, totaux en direct, confirmation).

## 3. Modèle de données

Toutes les nouvelles tables sont préfixées `agca_` et cohabitent avec les anciennes tables (conservées, non modifiées, non lues par le nouveau code sauf par le script d'import).

| Table | Colonnes principales |
|---|---|
| `agca_saison` | id, libelle (« 2026-27 »), date_debut, date_fin, statut (active / gelee) |
| `agca_serie` | id, code (M, H1), libelle, nb_parties, structure (json : ordre double/simple), pts_gagne, pts_nul, pts_perdu, index_min, index_max (null = libre), joker_h, joker_d, mixte (bool) |
| `agca_golf` | id, nom, ville, contact |
| `agca_equipe` | id, golf_id, serie_id, nom (« LUBERON-1 »), capitaine_nom, capitaine_prenom, capitaine_email, capitaine_tel, actif |
| `agca_utilisateur` | id, identifiant, serie_id, equipe_id (null si admin pur), hash_sha1 (legacy, null après migration), hash_bcrypt, est_admin, derniere_connexion, tentatives, bloque_jusqua |
| `agca_division` | id, saison_id, serie_id, libelle, ordre |
| `agca_division_equipe` | division_id, equipe_id, position (1..5) |
| `agca_journee` | id, saison_id, serie_id, numero (1..5), phase (aller / retour), date_calendrier |
| `agca_rencontre` | id, division_id, journee_id, recevant_id, invite_id, date_reelle, reportee (bool), statut (a_jouer / enregistree / forfait), forfaitaire_id, total_pour, total_contre, pts_rencontre_pour, pts_rencontre_contre, bonus_invite, alertes (json), enregistree_le, enregistree_par |
| `agca_partie` | id, rencontre_id, numero, type (double / simple), rec_joueur1_id, rec_index1, rec_sexe1, rec_joueur2_id, rec_index2, rec_sexe2, inv_joueur1_id, …, resultat (G / N / P vu du recevant), score_trous, score_restants, score_as, pts_pour, pts_contre |
| `agca_joueur` | id, golf_id, nom, prenom, sexe (H / D / null), dernier_index, cree_par, fusionne_dans |
| `agca_journal` | id, quand, utilisateur_id, action, cible_type, cible_id, detail (json) |
| `agca_relance` | id, rencontre_id, envoyee_le |

Les colonnes de totaux et de points sur `agca_rencontre` sont un cache régénéré à chaque enregistrement ou correction ; la source de vérité reste `agca_partie`. Le classement est toujours calculé à la volée depuis les rencontres enregistrées.

## 4. Domaine : règles métier

### 4.1 Grille des rencontres (`Domain\Grille`)

Entrée : liste des positions 1..n (n = 4 ou 5). Sortie : liste de (journée, phase, position recevante, position invitée).

Poule à 4 : aller J1 : 1-2, 3-4 · J2 : 2-3, 4-1 · J3 : 3-1, 4-2. Retour : mêmes journées, domiciles inversés. Dates : aller = journées 1 à 3 du calendrier de la série, retour = journées retour 1 à 3.

Poule à 5 (grille du site actuel, une équipe exempte par journée) :

| Journée | Aller | Retour |
|---|---|---|
| 1 | 1-2, 4-3 | 2-1, 3-4 |
| 2 | 3-1, 2-5 | 1-5, 2-3 |
| 3 | 1-4, 5-3 | 5-4, 1-3 |
| 4 | 5-1, 2-4 | 5-2, 4-1 |
| 5 | 3-2, 4-5 | 3-5, 4-2 |

Chaque paire se rencontre une fois aller et une fois retour, domiciles inversés. Dates : les 10 journées du calendrier.

### 4.2 Calcul d'une rencontre (`Domain\CalculRencontre`)

- Points de match par partie selon `resultat` : Mixte 2/1/0, H1 3/2/1 (paramètres de la série).
- `total_pour` = somme des pts recevant, `total_contre` = somme des pts invité.
- Points de rencontre : 3 / 2 / 1 selon total_pour >, =, < total_contre.
- Bonus invité : +1 si l'invité gagne, +0,5 si nul. Jamais de bonus au recevant.
- Forfait : total 15-0 pour le bénéficiaire, 0 pt rencontre au forfaitaire, 3 pts au bénéficiaire, +1 bonus si le bénéficiaire est l'invité.

### 4.3 Classement (`Domain\Classement`)

Par division, sur les rencontres au statut enregistrée ou forfait. Colonnes : points (rencontre + bonus), golf, bonus, joués, gagnés, nuls, perdus, forfaits, pour, contre, diff.

Ordre : 1) points totaux ; 2) confrontation directe entre ex æquo : somme des points de rencontre bonus inclus sur leurs matchs aller-retour joués ; 3) diff pour − contre sur la saison ; 4) ex æquo affichés au même rang. Pour un groupe de 3 ex æquo ou plus, la confrontation directe s'applique au mini-championnat entre eux.

### 4.4 Contrôles non bloquants (`Domain\Controles`)

Retourne une liste de codes stockés dans `rencontre.alertes` et affichés en pastille (suivi public, tableau de bord, admin) :

- `INDEX_HORS_BORNES` (Mixte) : joueur d'index < 11,5 ou > 22 qui n'est pas couvert par un joker.
- `JOKER_H_MULTIPLE`, `JOKER_D_MULTIPLE` (Mixte) : plus d'un homme ou plus d'une dame sous 11,5 dans l'équipe recevante ou l'équipe invitée.
- `FEUILLE_INCOMPLETE` : partie sans joueur ou sans résultat.
- `SCORE_INCOHERENT` : « AS » avec résultat ≠ nul, ou score avec trous d'avance et résultat = nul.
- `TOTAL_INCOHERENT` : somme des points ≠ nb_parties × pts_gagne (impossible si les parties sont toutes saisies, gardé pour les corrections admin).

### 4.5 Score structuré

`score_trous` (1..10), `score_restants` (0..9), `score_as` (bool). Affichage : « 3&2 », « 1 UP » (restants = 0), « AS ». Tous optionnels ; seul `resultat` est obligatoire.

## 5. Parcours

### Public
- Accueil provisoire (phase 1) avec accès aux deux séries ; texte « Homme 1re série ».
- Par série : classements de toutes les divisions ; suivi par division (journée, date calendrier, recevant, invité, date réelle si report, résultat POUR-CONTRE, pastille d'alerte, mention forfait) ; feuille vierge imprimable ; règlement (texte statique).
- Saisons passées : mêmes pages en lecture seule via un sélecteur de saison.

### Capitaine (connexion équipe + série + mot de passe)
- Tableau de bord : rencontres de la saison avec état : à saisir / en attente de l'adversaire / enregistrée / forfait.
- Saisie (recevant uniquement, rencontre à jouer) : date réelle pré-remplie ; blocs de parties selon la série (Mixte : 5 × [simple, simple, double] ; H1 : [double, simple × 4]) ; par joueur : nom avec auto-complétion sur `agca_joueur` du golf de l'équipe (création à la volée), index, H/D en Mixte ; résultat G/N/P ; score optionnel. Totaux et points affichés en direct. Récapitulatif puis Enregistrer. Enregistrement compté immédiatement au classement.
- Consultation en lecture seule de ses feuilles (recevant ou invité).
- Changement de mot de passe.

### Admin
- Tout ce que voit un capitaine, sur toutes les équipes.
- Corriger une feuille enregistrée ; déclarer / annuler un forfait ; modifier une date réelle. Journalisé.
- Liste des alertes en attente avec lien vers la feuille ; possibilité de marquer une alerte « vue ».
- Identifiants : réinitialiser un mot de passe (mot de passe temporaire affiché une fois), activer/retirer le drapeau admin, rattacher à une équipe.
- Joueurs : liste par golf, fusion de doublons (les parties sont réaffectées, l'ancien joueur reçoit `fusionne_dans`), correction nom / sexe.
- Nouvelle saison : série, équipes engagées, divisions (libellé libre), positions 1..5, dates des journées ; génération des rencontres. Gel de la saison précédente.

## 6. E-mails et relances

Envoi SMTP IONOS via PHPMailer, expéditeur `noreply@agca-amitie.org`, texte simple + lien. Paramètres dans `config.php`. **Tant que la boîte n'existe pas ou que `mail.enabled = false`, les messages sont écrits dans `agca_journal` (action `mail_simule`) au lieu d'être envoyés.** Un échec d'envoi n'annule jamais un enregistrement : l'envoi se fait après le commit et l'erreur est journalisée.

| Événement | Destinataires |
|---|---|
| Feuille enregistrée / corrigée | capitaine invité + admin |
| Report (date réelle modifiée) | capitaine invité + admin |
| Forfait déclaré | les deux capitaines + admin |
| Relance feuille non saisie | capitaine recevant, copie admin |

Relances : commande `bin/relances.php` exécutée chaque jour par le cron IONOS. Cible : rencontres `a_jouer` dont `date_reelle` + 48 h est passée ; envoi si aucune relance dans `agca_relance` depuis 2 jours. Aucune sanction automatique.

## 7. Sécurité

- Import des empreintes SHA-1 dans `hash_sha1`. À la première connexion réussie (comparaison SHA-1), calcul de `hash_bcrypt` et effacement de `hash_sha1`. Ensuite `password_verify` uniquement.
- Sessions PHP : cookie HttpOnly, Secure, SameSite=Lax ; régénération d'identifiant à la connexion.
- Jeton CSRF sur tous les formulaires ; requêtes préparées PDO partout ; échappement systématique dans les vues.
- Limitation : 5 échecs → blocage 15 minutes de l'identifiant.
- Autorisations vérifiées côté serveur à chaque requête : saisie = recevant et rencontre à jouer ; lecture feuille = capitaine concerné ou admin ; corrections = admin.

## 8. Import initial (`bin/import.php`, rejouable)

Depuis le dump `_DATABASEUU_.sql` chargé dans la base :

1. Golfs et équipes : `Mequipe` (VALGARDE-2 exclue), `H1equipe` limitée aux 4 équipes présentes dans `H1D1` (VALGARDE-1, SALON, ORANGE, VICTORIA) sauf liste corrigée par l'admin.
2. Utilisateurs : `Midentifiant` et `H1identifiant` (dernière ligne par nom d'équipe en cas de doublon) ; `ADMIN` devient l'utilisateur admin de Robert Michaux.
3. Saison 2026-27 active ; journées depuis `Mcalendrier` et `H1calendrier` (dates au format jour-mois converties en dates réelles 2026/2027).
4. Divisions et positions : reconstituées depuis les tables `MD1..MD4`, `MD7` et `H1D1` avec les libellés du site actuel ; génération des rencontres par `Domain\Grille`.
5. Report déjà saisi (ROQUEBRUNE – CHATEAU-L'ARC, 14/11/2026) repris sur la rencontre correspondante.
6. Aucun joueur importé.

## 9. Tests

- PHPUnit sur `Domain` : grille 4 et 5 (chaque paire une fois aller, une fois retour, exempt correct), calcul de rencontre (victoire, nul, défaite, forfait, bonus), classement (chaque niveau de départage, groupe de 3 ex æquo), contrôles, score structuré, migration SHA-1 → bcrypt.
- Tests d'intégration légers sur l'import (jeu réduit) et sur les autorisations des routes.
- Jeu de données de démonstration pour tests manuels sur téléphone.

## 10. Bascule (semaine du 15 septembre 2026)

1. Panneau IONOS : PHP 8.2+, MySQL 8 ; création de `noreply@agca-amitie.org` (à faire) ; tâche cron quotidienne.
2. Dépôt des fichiers par FTP/SSH, `config.php` renseigné, migrations puis import.
3. Test de connexion avec un capitaine volontaire, test d'une feuille sur le jeu de démonstration puis purge.
4. Remplacement de `index.html` par le nouvel accueil ; anciennes pages PHP laissées en place mais plus liées.
5. E-mail d'accès à tous les capitaines (identifiant inchangé, nouvelle adresse de connexion).

## 11. Hors périmètre phase 1

Coquille du site (accueil complet, golfs membres, organigramme, contact, mentions légales, statuts, AG, photos, compétitions individuelles) → phase 2. Statistiques → phase 3. D1, montées/descentes, validation de la feuille par l'invité, saisie structurée des compétitions individuelles : hors périmètre.
