# AGCA — Phase 2 : site complet (accueil, design, contenus éditables)

Date : 4 septembre 2026. Statut : à valider par Victoria (pour Robert Michaux). Complète la conception de la phase 1 (`2026-09-03-agca-refonte-design.md`), dont l'application, les tables `agca_*` et les règles restent inchangées.

## 1. Décisions prises pendant le cadrage

| Sujet | Décision |
|---|---|
| Périmètre | Tout le site public et sa gestion : accueil, gabarit et design, golfs membres, cinq compétitions individuelles, photos, organigramme, statuts, assemblées générales, voyage, mentions légales, contact, actualités. Habillage des pages championnat de la phase 1 dans le même design. |
| Gestion des contenus | Mini-gestion de contenu complète dans l'espace admin (option 2) : l'admin édite lui-même tous les textes, actualités, palmarès, golfs, organigramme, albums et documents. |
| Design | Inspiré de la maquette Lovable pour le style uniquement (photo pleine largeur, titres en serif, vert profond et beige, chiffres clés, cartes). Aucune donnée ni texte de cette maquette n'est repris. |
| Textes et données | Ceux de l'ancien site `agca-amitie.org` : présentation, textes des séries, compétitions (documents Google), mentions légales (document Google), liste des golfs (feuille Google, complétée par les golfs des équipes 2026-27), albums photo existants. |
| Visuels | Photo de golf libre de droits (bandeau d'accueil, voir §2), logo AGCA actuel conservé. |
| Calendrier | Tout en ligne ensemble la semaine du 15 septembre 2026 (option 1) : maquette validée d'abord, puis deux chantiers en parallèle (Contenu, Design), puis intégration. |

## 2. Design

### Identité
- Couleurs : vert profond `#1f3d2e` (en-tête, bandeau, pied), vert `#2f6a4f` (liens, boutons), beige `#f4efe6` (sections alternées), ocre `#c9a24d` (chiffres clés, filets), encre `#1a1a1a`, blanc.
- Typographie : titres en sans-serif moderne « Manrope » (700 et 800, chiffres clés en 800), texte en « Inter » (400 et 600), auto-hébergées dans `public/fonts/` (aucun appel à Google Fonts en production, conformité RGPD), avec repli système. Rendu haut de gamme et sobre : graisses fortes, interlettrage serré sur les titres, étiquettes en petites capitales ocre.
- Logo : `public/img/logo-agca.jpg` (150 × 150 actuel), affiché à 40 px dans l'en-tête à côté du mot « AGCA » et de « Association des Golfs de la Coupe de l'Amitié » en petites capitales.
- Photo : `public/img/hero-golf.jpg` (1920 × 1280, 350 Ko) + variante 960 px pour mobile. La génération d'image n'étant pas disponible, photo libre du parcours de Cannes-Mougins (Wikimedia Commons, licence CC BY-SA 4.0, crédit yourgolftravel.com) ; crédit affiché dans les mentions légales. Remplaçable par une photo de l'association depuis le dossier `public/img/`.

### Gabarit commun (toutes les pages, y compris championnat et admin)
- En-tête : logo + nom, menu : Championnats (Mixte 2e série, Homme 1re série), Compétitions, Golfs membres, Photos, Association (Organigramme, Statuts, Assemblées générales, Voyage, Contact), bouton « Espace capitaine » (ou nom de l'équipe + Admin + Déconnexion une fois connecté). Menu déroulant au survol sur ordinateur, menu « burger » sur mobile (bouton + JavaScript minimal, utilisable sans souris).
- Pied de page : adresse de l'association (c/o Robert Michaux, 11 rue de la Verrerie, 13100 Aix-en-Provence, téléphone, e-mail), liens Mentions légales, Contact, FFGolf, année.
- Composants : cartes, tableaux (classements, calendrier), pastilles, boutons, formulaires, étiquettes de section en petites capitales ocre, titres serif. Une seule feuille `public/css/agca.css` réécrite avec des variables CSS ; points de rupture 640 px et 1024 px.
- Impression : gabarit allégé (déjà en place pour la feuille vierge).

### Accueil
Sections, de haut en bas :
1. Bandeau photo avec « Interclubs · Provence-Alpes-Côte d'Azur », le titre « Association des Golfs de la Coupe de l'Amitié », une phrase d'accroche, boutons « Classements et calendrier » et « Espace capitaine », puis les chiffres clés calculés : golfs membres (golfs actifs), équipes engagées (équipes de la saison active), journées de championnat (dates distinctes du calendrier de la saison active), compétitions individuelles (5).
2. Bandeau citation « Le sport relie les gens, construit des amitiés et avant tout, vous permet de vous faire plaisir. » avec lien vers https://www.ffgolf.org/.
3. Storytelling (textes de l'ancien site, éditables dans la page `accueil-presentation`) : « Inter-clubs par équipe », « Amitié 2e série mixte », « Amitié Homme 1re série », « Autres compétitions », et l'encart vert « Les Coupes de l'Amitié » avec le texte de présentation de l'association.
4. Bandeau « Prochaine journée » : première date à venir du calendrier de la saison active, avec la série et le nombre de rencontres, lien vers le suivi.
5. Classements en cours : pour chaque série, la première division (5 premières lignes : rang, équipe, joués, points) et un lien « Toutes les divisions ».
6. Prochaines rencontres : les 6 prochaines rencontres à jouer, toutes séries, avec date, recevant, invité, division.
7. Nos compétitions : cinq cartes (nom, accroche, formule, dernier vainqueur du palmarès) reprenant le texte de l'ancien site (« exclusivement réservées aux joueurs des clubs membres ayant participé au moins une fois… »).
8. Actualités : les trois dernières publiées (date, titre, résumé), lien vers la liste.
9. Golfs membres : grille des golfs actifs (nom, ville), lien vers la page.

### Pages publiques
| Route | Contenu |
|---|---|
| `/` | Accueil |
| `/competitions` et `/competitions/{code}` | Présentation des cinq compétitions ; page par compétition : description, palmarès par saison (texte + document éventuel) |
| `/golfs` | Grille des golfs membres : nom, ville, site web |
| `/photos` | Albums : titre, année, lien vers la galerie existante (`/imagesTROPHEE_2025/` etc., conservées sur le serveur) |
| `/actualites` et `/actualites/{id}-{slug}` | Liste et article |
| `/association/organigramme` | Bureau directeur puis conseil d'administration : fonction, prénom, nom, golf |
| `/association/statuts`, `/association/assemblees-generales`, `/association/voyage` | Page de texte + documents joints (PDF/DOC) |
| `/mentions-legales` | Page de texte |
| `/contact` | Formulaire : nom, e-mail, objet, message → e-mail à l'admin (`mail.admin`), copie du message au demandeur ; jeton CSRF, champ piège anti-robot, une soumission par minute et par session ; journalisé |
| `/page/{slug}` | Toute page de texte supplémentaire créée par l'admin |
Les pages championnat de la phase 1 gardent leurs routes et reçoivent le nouveau gabarit.

## 3. Modèle de contenu (nouvelles tables, migration `003_contenu.sql`)

| Table | Colonnes principales |
|---|---|
| `agca_page` | id, slug (unique), titre, corps_md, corps_html, dans_menu (bool), ordre, modifie_le, modifie_par |
| `agca_actualite` | id, titre, slug, date_publication, resume, corps_md, corps_html, publie (bool), cree_le, modifie_le, modifie_par |
| `agca_competition` | id, code (challenge, tournoi, trophee, master, coupe-capitaines), nom, accroche, formule, corps_md, corps_html, ordre, actif |
| `agca_palmares` | id, competition_id, saison (texte « 2025 »), lieu, vainqueur, detail_md, detail_html, document_id (nullable), ordre |
| `agca_golf` (existante, colonnes ajoutées) | ville, site_web, membre (bool, défaut 1), ordre |
| `agca_organigramme` | id, groupe (bureau / ca), fonction, prenom, nom, golf, ordre |
| `agca_album` | id, titre, annee, url, ordre |
| `agca_document` | id, titre, fichier (nom sur disque dans `public/documents/`), type_mime, taille, categorie (statuts / ag / voyage / palmares / autre), televerse_le, televerse_par |
| `agca_page_document` | page_id, document_id, ordre |

Texte enrichi : l'admin écrit en Markdown réduit (titres `#`/`##`, paragraphes, **gras**, *italique*, liens, listes à puces et numérotées, tableaux simples `| a | b |`, sauts de ligne). Un rendu `Domain\Markdown` (sans dépendance, 150 lignes) échappe d'abord tout HTML puis applique la syntaxe ; le HTML rendu est stocké à l'enregistrement (`corps_html`) et jamais recalculé à l'affichage. Aperçu en direct dans l'éditeur (JavaScript côté client, même grammaire).

Documents : téléversement PDF / DOC / DOCX / XLS / XLSX / JPG / PNG, 10 Mo maximum, nom de fichier régénéré (`aaaa-mm-jj-slug-aleatoire.ext`), type vérifié par `finfo`, stockés dans `public/documents/` (dossier exclu de git, à sauvegarder). Suppression possible si aucun palmarès ni page ne l'utilise.

## 4. Espace admin : gestion de contenu

Nouvelle entrée « Contenu du site » sur `/admin`, avec :
- `/admin/pages` : liste, édition (`/admin/pages/{id}`), création ; les pages système (accueil-presentation, statuts, assemblees-generales, voyage, mentions-legales) ne peuvent pas être supprimées ni renommées.
- `/admin/actualites` : liste, création, édition, publication / dépublication, suppression.
- `/admin/competitions` : édition des cinq fiches et de leur palmarès (ajout, modification, suppression d'une ligne).
- `/admin/golfs` : liste et édition (ville, site web, membre, ordre), création.
- `/admin/organigramme` : lignes bureau / conseil d'administration, ordre.
- `/admin/albums` : lignes titre / année / URL.
- `/admin/documents` : téléversement, liste, suppression.
Toutes les actions : `exigerAdmin()`, jeton CSRF, journal `agca_journal` (action, cible, auteur). Éditeur : zone Markdown + aperçu + aide-mémoire de la syntaxe.

## 5. Contenu initial (`db/contenu/initial.php` + `bin/contenu-initial.php`, rejouable)

- Pages : `accueil-presentation` (textes de l'accueil actuel : Inter-clubs par équipe, Amitié 2e série mixte, Homme 1re série corrigé, Autres compétitions, Les Coupes de l'Amitié), `mentions-legales` (document Google actuel converti), `statuts`, `assemblees-generales`, `voyage` (texte court + lien vers le document Word existant), avec la mention « À compléter par l'administrateur » pour les pages vides de l'ancien site.
- Compétitions : cinq fiches ; accroche et formule rédigées à partir des documents actuels ; palmarès : dernières éditions extraites des documents Google (Challenge 2019, Coupe des capitaines 2022, Master 2025, Tournoi 2025, Trophée 2026) avec le texte de compte rendu en `detail_md`.
- Golfs : les 18 golfs des équipes 2026-27 + Cabre d'Or (membre de la liste actuelle), sites web depuis la feuille Google ; ville à compléter par l'admin quand inconnue.
- Albums : les 24 galeries existantes (`imagesCHALLENGE2018` … `imagesanniversaire35ans`) avec titre et année déduits du nom du dossier.
- Organigramme : vide, à saisir par l'admin (l'ancien site n'a aucune donnée).

## 6. Architecture technique

- Même socle PHP 8 sans framework. Nouveaux dépôts : `PageRepository`, `ActualiteRepository`, `CompetitionRepository` (+ palmarès), `OrganigrammeRepository`, `AlbumRepository`, `DocumentRepository` ; `GolfRepository` étendu. Nouveaux services : `Domain\Markdown`, `App\Service\Documents` (téléversement), `App\Service\Accueil` (chiffres clés, prochaine journée, prochaines rencontres, extraits de classement), `App\Service\Contact`. Nouveaux contrôleurs : `SiteController` (public), `ContenuController` (admin).
- Gabarit : `templates/layout.php` réécrit, composants dans `templates/partials/` (menu, pied, carte, étiquette). Les templates existants (public, capitaine, feuille, admin) sont adaptés au nouveau gabarit sans changer leur logique.
- Contrat entre les deux chantiers : le chantier Contenu livre les tables, dépôts, services et écrans admin ; le chantier Design livre le gabarit, la feuille de style, l'accueil et les pages publiques en consommant les dépôts. Les signatures des dépôts sont fixées dans le plan avant le démarrage ; le chantier Design développe contre un jeu de données de démonstration fourni par `bin/demo.php` enrichi.
- Sécurité : échappement systématique, Markdown rendu à partir de texte échappé (aucun HTML brut accepté), téléversement contrôlé (type, taille, nom), documents servis comme fichiers statiques, formulaire de contact protégé.
- Performance : aucune dépendance front, images optimisées, polices auto-hébergées, une requête par section d'accueil.

## 7. Tests

- `Domain\Markdown` : unitaires (chaque construction, échappement d'HTML injecté, liens `javascript:` refusés).
- Dépôts de contenu et service Accueil : intégration sur `agca_test`.
- Contact : envoi simulé, piège anti-robot, limitation.
- Téléversement : type refusé, taille refusée, nom régénéré.
- Pages publiques et admin : tests de fumée HTTP (200, contenu attendu, 403 sans admin, CSRF).
- Revue visuelle : maquette validée par le client avant développement ; vérification ordinateur / mobile en fin de chantier.

## 8. Bascule (complète `docs/bascule.md`)

- Migration `003_contenu.sql` puis `bin/contenu-initial.php` après l'import de la phase 1.
- Dossier `public/documents/` créé, inscriptible par PHP, sauvegardé.
- Anciennes galeries (`images*/`) et fichiers (`VOYAGE-AGCA.doc`, `M_FEUILLE MODIFICATION RENCONTRE 2016.doc`) conservés à la racine web et référencés par les albums / pages.
- L'ancien `index.html` disparaît : la racine du domaine sert le nouvel accueil.

## 9. Hors périmètre
Statistiques joueurs (phase 3), galeries photo nouvelles (téléversement de photos), multilingue, recherche, newsletter.
