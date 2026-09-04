<?php
declare(strict_types=1);
// Contenu initial repris de l'ancien site (Google Docs de l'AGCA, maquette docs/maquette/accueil.body.html).
// Sources brutes : db/contenu/sources/*.txt (challenge, coupe_capitaines, master, tournoi, trophee, mentions),
// db/contenu/sources/golfs-membres.csv. Textes nettoyés (casse, espacement, lignes techniques Google Docs retirées) ;
// aucune donnée inventée. Rejouable : ContenuInitial n'écrase rien d'existant.
return [
    'pages' => [
        [
            'slug' => 'accueil-accroche',
            'titre' => 'Accroche du bandeau',
            'corps_md' => "Le golf par équipes entre clubs amis, de septembre à mai, dans l'esprit de Saint Andrews et toujours autour d'une bonne table.",
        ],
        [
            'slug' => 'accueil-esprit',
            'titre' => 'Découvrir de nouveaux parcours, et de nouveaux amis',
            'corps_md' => "Nous organisons des rencontres interclubs par matchs aller-retour, ainsi que des compétitions individuelles réservées à nos membres. Les matchs se jouent dans un esprit sportif, dans le respect des règles de Saint Andrews, et se finissent toujours de façon conviviale autour d'un bon repas.",
        ],
        [
            'slug' => 'accueil-encart',
            'titre' => "Une saison à l'AGCA",
            'corps_md' => <<<'MD'
- **Des parcours variés**, de Gap à Sainte-Maxime, à découvrir en équipe.
- **Une saison complète**, le samedi, en poules de quatre équipes, aller et retour.
- **Cinq rendez-vous individuels** réservés aux joueurs des clubs membres.

Votre club souhaite engager une équipe ? [Écrivez-nous](/contact).
MD,
        ],
        [
            'slug' => 'format-interclubs',
            'titre' => 'Inter-clubs par équipe',
            'corps_md' => 'De septembre à mai, pour entraîner joueurs et joueuses en basse saison et les préparer aux championnats fédéraux de France, de Ligue et aux Grands Prix. Divisions généralement de 4 équipes, rencontres le samedi en aller-retour, soit 6 rencontres par an.',
        ],
        [
            'slug' => 'format-mixte',
            'titre' => 'Amitié 2e série mixte',
            'corps_md' => 'Équipes de 10 joueurs, dames et hommes. Matchs en net : 9 trous en double et 9 trous en simple. Index compris entre 11,5 et 22, plus un joker homme et une joker dame possibles avec un index inférieur.',
        ],
        [
            'slug' => 'format-h1',
            'titre' => 'Amitié Homme 1re série',
            'corps_md' => 'Équipes de joueurs classés par ordre des index. Matchs en brut : 1 double et 4 simples sur 18 trous. Index non limités.',
        ],
        [
            'slug' => 'format-individuelles',
            'titre' => 'Compétitions individuelles',
            'corps_md' => 'Master, Challenge, Trophée, Tournoi et Coupe des Capitaines : réservées aux joueurs des clubs membres ayant participé au moins une fois à une rencontre par équipes.',
        ],
        [
            'slug' => 'citation',
            'titre' => 'Citation',
            'corps_md' => 'Le sport relie les gens, construit des amitiés et avant tout, vous permet de vous faire plaisir.',
        ],
        [
            'slug' => 'statuts',
            'titre' => 'Statuts',
            'corps_md' => "*À compléter par l'administrateur : le site précédent ne contenait pas ce texte. Les documents peuvent être joints depuis l'espace admin.*",
            'dans_menu' => 1,
            'ordre' => 2,
        ],
        [
            'slug' => 'assemblees-generales',
            'titre' => 'Assemblées générales',
            'corps_md' => "*À compléter par l'administrateur : le site précédent ne contenait pas ce texte. Les documents peuvent être joints depuis l'espace admin.*",
            'dans_menu' => 1,
            'ordre' => 3,
        ],
        [
            'slug' => 'voyage',
            'titre' => 'Voyage',
            'corps_md' => 'Le programme du voyage est à télécharger : [Voyage AGCA (document Word)](/VOYAGE-AGCA.doc).',
            'dans_menu' => 1,
            'ordre' => 4,
        ],
        [
            'slug' => 'mentions-legales',
            'titre' => 'Mentions légales',
            'dans_menu' => 0,
            'corps_md' => <<<'MD'
# Politique de confidentialité

## Formulaires de contact

Quand vous laissez un commentaire ou un mail sur notre site web via notre formulaire de contact, les données inscrites, mais aussi votre adresse IP et l'agent utilisateur de votre navigateur ne sont pas collectés.

## Médias

Si vous êtes un utilisateur ou une utilisatrice reconnus et que vous souhaitez télé-verser des images sur notre site web via notre Webmaster, nous vous conseillons d'éviter les images contenant des données EXIF de coordonnées GPS. Les visiteurs de votre site web peuvent télécharger et extraire des données de localisation depuis ces images.

## Contenu embarqué depuis d'autres sites

Les articles de ces sites peuvent inclure des contenus intégrés (par exemple des vidéos, images, articles). Le contenu intégré depuis d'autres sites se comporte de la même manière que si le visiteur se rendait sur cet autre site. Ces sites web pourraient collecter des données sur vous, utiliser des cookies, embarquer des outils de suivi tiers, suivre vos interactions avec ces contenus embarqués si vous disposez d'un compte connecté sur leur site web.

## Statistiques et mesures d'audience

Afin de suivre l'audience de notre site internet, nous utilisons un compteur de visite standard.

## Utilisation et transmission de vos données personnelles

Les données personnelles collectées auprès des utilisateurs ont pour objectif la mise à disposition des services de notre site internet, leur amélioration et le maintien d'un environnement sécurisé. La base légale des traitements est l'exécution du contrat entre l'utilisateur et le site internet. Plus précisément, les utilisations sont les suivantes :

- accès et utilisation du site internet par l'utilisateur ;
- gestion du fonctionnement et optimisation du site internet ;
- mise en œuvre d'une assistance utilisateurs ;
- vérification, identification et authentification des données transmises par l'utilisateur ;
- gestion des éventuels litiges avec les utilisateurs ;
- envoi d'informations commerciales et publicitaires, en fonction des préférences de l'utilisateur.

Les données personnelles enregistrées ne sont en aucun cas transmises à des tiers (sauf accord préalable de l'utilisateur). Seuls l'éditeur, les administrateurs du site internet et l'hébergeur ont accès aux données personnelles.

Nota : les commentaires des visiteurs peuvent être vérifiés à l'aide d'un service automatisé de détection des commentaires indésirables.

## Les droits que vous avez sur vos données

En application de la réglementation applicable aux données à caractère personnel, les utilisateurs disposent des droits suivants :

- Le droit d'accès : ils peuvent exercer leur droit d'accès, pour connaître les données personnelles les concernant, en écrivant à l'adresse électronique ci-dessous. Dans ce cas, avant la mise en œuvre de ce droit, l'éditeur peut demander une preuve de l'identité de l'utilisateur afin d'en vérifier l'exactitude.
- Le droit de rectification : si les données à caractère personnel détenues par l'éditeur sont inexactes, ils peuvent demander la mise à jour des informations.
- Le droit de suppression des données : les utilisateurs peuvent demander la suppression de leurs données à caractère personnel, conformément aux lois applicables en matière de protection des données.
- Le droit à la limitation du traitement : les utilisateurs peuvent demander à l'éditeur de limiter le traitement des données personnelles conformément aux hypothèses prévues par le RGPD.
- Le droit de s'opposer au traitement des données : les utilisateurs peuvent s'opposer à ce que ses données soient traitées conformément aux hypothèses prévues par le RGPD.
- Le droit à la portabilité : ils peuvent réclamer que l'éditeur leur remette les données personnelles fournies pour les transmettre à une nouvelle plateforme.

## Comment nous protégeons vos données

Tous les mots de passe sont cryptés.

## Informations de contact

Pour toutes vos demandes relatives à la protection des données personnelles, vous pouvez nous contacter à l'adresse suivante : AGCA, Robert Michaux, 11 rue de la Verrerie, 13100 Aix-en-Provence. Ou par email à l'adresse : agca@agca-amitie.org

# Mentions légales

## Conception et production

Le site internet AGCA est édité par Robert Michaux, 11 rue de la Verrerie, 13100 Aix-en-Provence. Directeur de la publication : Robert Michaux, webmaster. Ce site internet a été réalisé par Robert Michaux en PHP. Hébergement : 1&1 Internet SARL, Service Comptable, 7 place de la Gare, BP 70109, 57201 Sarreguemines Cedex.

## Protection des données personnelles

Les seules données personnelles apparaissant sur notre site résultent de la communication volontaire de nos capitaines. Il s'agit de leur adresse de courrier électronique, d'un numéro de téléphone et d'une adresse, déposés dans « l'espace capitaine » grâce à leur code personnel, qui est crypté. L'usage de ces informations est strictement réservé aux seuls capitaines ainsi qu'au bureau directeur de notre association AGCA pour leurs communications internes. Ils ne font l'objet d'aucune communication extérieure.

De plus, les données personnelles recueillies sur ce site résultent de la communication volontaire d'une adresse de courrier électronique lors du dépôt d'un message électronique. Les données ainsi recueillies ne servent qu'à transmettre les éléments d'informations demandés et n'ont que pour seul destinataire l'AGCA, auprès de qui peuvent s'exercer les droits d'accès et de rectification, conformément à l'article 27 de la loi n° 78-17 du 6 janvier 1978 relative à l'informatique, aux fichiers et aux libertés.

## Utilisation des cookies

Notre site n'utilise pas de cookie.

## Droits d'auteur

Les informations, pictogrammes, photographies, images, textes, séquences vidéo, et autres documents présents sur le site Internet sont protégés par la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. À ce titre, toute reproduction, représentation, adaptation, ou modification, partielle ou intégrale, ou transfert sur un autre site, sont interdits.

La copie sur support papier à usage privé de ces différents objets de droits est autorisée conformément à l'article L122-5 du Code de la propriété intellectuelle. Leur reproduction partielle ou intégrale, sans l'accord préalable et écrit de l'auteur, est strictement interdite.

Les bases de données figurant sur le site Internet sont protégées par les dispositions de la loi du 11 juillet 1998 portant transposition dans le Code de la propriété intellectuelle de la directive européenne du 11 mars 1996 relative à la protection juridique des bases de données. Sont notamment interdites l'extraction et la réutilisation, partielles ou complètes, du contenu des bases de données du site.

## Liens hypertextes

Les liens hypertextes mis en place dans le cadre de ce site Internet en direction d'autres ressources présentes sur le réseau Internet sont proposés uniquement pour vous apporter un complément d'informations. Ni leur contenu ou les liens qu'ils contiennent, ni les changements ou mises à jour qui leur sont apportés ne sauraient engager la responsabilité de l'association. L'accès aux sites externes reliés à notre site est réalisé sous votre entière responsabilité.

## Responsabilité

L'AGCA ne peut garantir ni l'exactitude, ni l'exhaustivité des informations mises en ligne. En conséquence, nous déclinons toute responsabilité pour toute imprécision, inexactitude ou omission portant sur des informations disponibles. Nous nous réservons le droit de modifier ou de corriger le contenu de ce site à tout moment et ceci sans préavis, sans que cela puisse donner lieu à un quelconque droit à dédommagement ou indemnité.

Photo du bandeau : parcours de Cannes-Mougins, yourgolftravel.com, licence CC BY-SA 4.0 (Wikimedia Commons).
MD,
        ],
    ],
    'competitions' => [
        [
            'code' => 'challenge',
            'nom' => 'Le Challenge',
            'accroche' => 'Ouverture de saison',
            'formule' => 'Par équipes de club, classements « Challenge » et « 4 cartes ».',
            'corps_md' => 'Réservée aux joueurs des clubs membres ayant participé au moins une fois à une rencontre par équipes.',
            'ordre' => 1,
            'palmares' => [
                [
                    'saison' => '2019',
                    'lieu' => 'Châteaublanc',
                    'vainqueur' => "Aix Provence 1 (Challenge) · Frégate (4 cartes)",
                    'detail_md' => <<<'MD'
Ce 27ème Challenge a eu lieu comme prévu sur le golf de Châteaublanc le 10 septembre, et ce ne sont pas les ondées prévues ce jour qui ont gêné les 16 équipes sur les 19 engagées d'être présentes. Neuf ont joué pour le classement « Challenge » et sept pour celui du « 4 cartes ».

Félicitations à tous les vainqueurs. Merci aux équipes (Direction, Accueil et Gestion, Terrain, Restauration) du golf de Châteaublanc ainsi qu'aux membres du Bureau Directeur pour leur implication dans l'organisation.

## Classement « Challenge » (avec pro)

| Place | Golf | Points |
| --- | --- | --- |
| 1ère | Aix Provence 1 | 147 |
| 2ème | Orange 2 | 134 |
| 3ème | Châteaublanc | 132 |
| 4ème | Cabre d'Or | 129 |
| 5ème | Valcros | 126 |
| 6ème | Valgarde 1 | 124 |
| 7ème | École de l'Air | 122 |
| 8ème | Aix Provence 2 | 113 |
| 9ème | Saumane | 104 |

## Classement « 4 cartes » (sans pro)

| Place | Golf | Points |
| --- | --- | --- |
| 1ère | Frégate | 126 |
| 2ème | St Martin 3 | 117 |
| 3ème | Valberg | 117 |
| 4ème | St Martin 1 | 110 |
| 5ème | St Martin 2 | 96 |
| 6ème | Orange 1 | 89 |
| 7ème | Valgarde 2 | 83 |

Forfaits : Luberon, Gde Bastide.
MD,
                ],
            ],
        ],
        [
            'code' => 'tournoi',
            'nom' => "Le Tournoi de l'Amitié",
            'accroche' => 'Individuel',
            'formule' => 'Classement par équipe en net et classements individuels.',
            'corps_md' => 'Réservée aux joueurs des clubs membres ayant participé au moins une fois à une rencontre par équipes.',
            'ordre' => 2,
            'palmares' => [
                [
                    'saison' => '2025',
                    'lieu' => 'Valcros',
                    'vainqueur' => 'Valcros, 146 points',
                    'detail_md' => <<<'MD'
Un grand merci à toutes celles et ceux qui ont participé à cette belle manifestation et à la Direction du golf de Valcros et toutes ses équipes pour la réception, ainsi qu'aux organisateurs de cette compétition : Gérard Comazzetto, Thierry Dumont et Gaétan Carpentier.

## Classement par équipe en net

| Rang | Équipe | Points |
| --- | --- | --- |
| 1er | Valcros | 146 |
| 2e | Aix-en-Provence | 139 |
| 3e | Valgarde 1 | 138 |
| 4e | Sainte-Maxime | 136 |
| 5e | Valgarde 2 | 131 |
| 6e | Saint-Martin de Crau 2 | 125 |
| 7e | Saint-Martin de Crau 1 | 120 |
| 8e | Luberon | 119 |
| 9e | École de l'Air | 114 |
| 10e | Orange | 107 |

## Classement par équipe en brut

| Rang | Équipe | Points |
| --- | --- | --- |
| 1er | Valcros | 91 |
| 2e | Valgarde 1 | 90 |
| 3e | Aix-en-Provence | 87 |
| 4e | Sainte-Maxime | 70 |
| 5e | Saint-Martin de Crau 1 | 65 |
| 6e | École de l'Air | 64 |
| 7e | Luberon | 64 |
| 8e | Valgarde 2 | 64 |
| 9e | Saint-Martin de Crau 2 | 62 |
| 10e | Orange | 53 |

## Individuel

- 1ère brut dames : Martine Didier, 20 points, Aix-en-Provence.
- 1ère net dames : Véronique Aldeguer, 40 points, Valcros.
- 1er brut messieurs : Henri Aldeguer, 28 points, Valcros.
- 1er net messieurs : Guy Pomet, 41 points, Valgarde.
- 2e net messieurs : Fabrice Mamone, 41 points, Saint-Martin de Crau.
MD,
                ],
            ],
        ],
        [
            'code' => 'trophee',
            'nom' => 'Le Trophée',
            'accroche' => 'Par paires',
            'formule' => 'Stableford net et brut, messieurs et mixte.',
            'corps_md' => 'Réservée aux joueurs des clubs membres ayant participé au moins une fois à une rencontre par équipes.',
            'ordre' => 3,
            'palmares' => [
                [
                    'saison' => '2026',
                    'lieu' => 'Valgarde',
                    'vainqueur' => "Château-l'Arc et Valgarde (net et brut)",
                    'detail_md' => <<<'MD'
Les primes : un green fee gratuit offert pour chaque lauréat.

## Résultat net messieurs

42 stableford de Château-l'Arc : Quintero Carlos (12.4), Cerutti Daniel (14.1).

## Résultat net mixte

39 stableford de Valgarde : Naudin Marc (18.5), Naudin Christine (20).

## Résultat brut messieurs

34 stableford brut de Château-l'Arc : Lizot Patrick (11.0), Roux Michael (14.3).

## Résultat brut mixte

31 stableford brut de Valgarde : Tarrusson Edith (12.5), Renoux Bernard (18.9).

Résultats complets net et brut en PJ. Merci aux 8 clubs sur 21 qui étaient présents !

Très cordialement, Guy Pomet, Président de l'AGCA.
MD,
                ],
            ],
        ],
        [
            'code' => 'master',
            'nom' => 'Le Master',
            'accroche' => 'Clôture de saison',
            'formule' => 'Réservé aux équipes premières de chaque division.',
            'corps_md' => 'Réservée aux joueurs des clubs membres ayant participé au moins une fois à une rencontre par équipes.',
            'ordre' => 4,
            'palmares' => [
                [
                    'saison' => '2025',
                    'lieu' => 'Gap',
                    'vainqueur' => 'Valgarde (1re série) · Orange (2e série)',
                    'detail_md' => <<<'MD'
Voici le bilan de cette belle journée des Masters du 11 octobre à Gap. Le temps était excellent, le parcours était parfait, la convivialité présente. Valgarde a remporté le titre en 1ère série. Orange a arraché le titre en 2ème série au départage du 9ème score. La saison 2024-2025 sera clôturée samedi 18 par le Tournoi à Valcros.

Très cordialement, Guy Pomet, Président de l'AGCA.

2 clubs 1ère série + 5 clubs 2ème série (13 dames et 48 messieurs).

## 1ère Série

| Rang | Club | Points |
| --- | --- | --- |
| 1 (vainqueur) | Valgarde | 121 |
| 2 | Salon | 83 |
| 3 | Victoria | Forfait |

Individuel Brut : 1. Gori (Valgarde), 31 — 2. Cavdar (Valgarde), 25.

## 2ème Série

| Rang | Club | Points |
| --- | --- | --- |
| 1 (vainqueur) | Orange | 257 |
| 2 | Luberon | 257 |
| 3 | Digne | 232 |
| 4 | Grande Bastide | 230 |
| 5 | Roquebrune | 230 |

Individuel Net Dames : Sauvy (Luberon), 36.
Individuel Net Messieurs : Gondran Gabriel (Orange), 38.
MD,
                ],
            ],
        ],
        [
            'code' => 'coupe-capitaines',
            'nom' => 'La Coupe des Capitaines',
            'accroche' => 'Entre capitaines',
            'formule' => 'Une journée conviviale suivie de la réunion des capitaines.',
            'corps_md' => 'Réservée aux joueurs des clubs membres ayant participé au moins une fois à une rencontre par équipes.',
            'ordre' => 5,
            'palmares' => [
                [
                    'saison' => '2022',
                    'lieu' => 'Calas',
                    'vainqueur' => null,
                    'detail_md' => <<<'MD'
Cette première Coupe des Capitaines s'est déroulée le 21 juin sur le golf de Calas, suivie d'un déjeuner très convivial et de la réunion. Cette formule a été appréciée par tous.

Félicitations à tous, et tout spécialement aux vainqueurs.
MD,
                ],
            ],
        ],
    ],
    'golfs' => [
        ['nom' => 'AIX-EN-PROVENCE', 'ville' => 'Aix-en-Provence', 'site_web' => 'www.golf-aixenprovence.fr'],
        ['nom' => 'BARBAROUX', 'ville' => 'Brignoles', 'site_web' => null],
        ['nom' => "CHATEAU-L'ARC", 'ville' => 'Fuveau', 'site_web' => null],
        ['nom' => 'CHATEAUBLANC', 'ville' => 'Avignon', 'site_web' => null],
        ['nom' => 'DIGNE', 'ville' => 'Digne-les-Bains', 'site_web' => 'www.ngf-golf.com/gardengolf-digne'],
        ['nom' => 'ESTEREL', 'ville' => 'Saint-Raphaël', 'site_web' => 'esterel.bluegreen.com'],
        ['nom' => 'FREGATE', 'ville' => 'Saint-Cyr-sur-Mer', 'site_web' => 'www.dolcefregate-golf-provence.com'],
        ['nom' => 'GAP', 'ville' => 'Gap-Bayard', 'site_web' => 'www.gap-bayard.com'],
        ['nom' => 'GRANDE BASTIDE', 'ville' => 'Châteauneuf-Grasse', 'site_web' => 'www.opengolfclub.com/fr/golfs/fiche/3/Golf-de-la-Grande-Bastide/20'],
        ['nom' => 'LUBERON', 'ville' => 'Pierrevert', 'site_web' => 'www.golfduluberon.com'],
        ['nom' => 'ORANGE', 'ville' => 'Orange', 'site_web' => 'www.golforange.fr'],
        ['nom' => 'ROQUEBRUNE', 'ville' => 'Roquebrune-sur-Argens', 'site_web' => null],
        ['nom' => 'SAINT-MARTIN', 'ville' => 'Saint-Martin-de-Crau', 'site_web' => 'www.golfclubsaintmartinois.com'],
        ['nom' => 'SAINTE-MAXIME', 'ville' => 'Sainte-Maxime', 'site_web' => 'sainte-maxime.bluegreen.com'],
        ['nom' => 'SALON', 'ville' => 'Salon-de-Provence', 'site_web' => 'www.golfecoledelair.fr'],
        ['nom' => 'VALCROS', 'ville' => 'La Londe-les-Maures', 'site_web' => 'membres-asgv.over-blog.com'],
        ['nom' => 'VALGARDE', 'ville' => 'La Crau', 'site_web' => 'www.golf-valgarde.com'],
        ['nom' => 'VICTORIA', 'ville' => 'Cannes', 'site_web' => 'www.victoria-golfclub.com'],
        ["nom" => "CABRE D'OR", 'ville' => 'Cabriès', 'site_web' => 'www.golflacabredor.fr'],
    ],
    // Albums : une galerie par édition attestée dans db/contenu/sources/{challenge,master,tournoi,trophee}.txt
    // (années identifiées par les en-têtes « <COMPETITION> <ANNEE> » de chaque document), plus l'album anniversaire
    // cité dans la spec. Aucune galerie « Voyage » n'est créée : aucune édition n'est documentée dans les sources.
    // Voir task-5-report.md pour le détail de cette reconstruction (la spec ne liste pas les 24 dossiers annoncés).
    'albums' => [
        ['titre' => 'Challenge 2019', 'annee' => 2019, 'url' => '/imagesCHALLENGE_2019/'],
        ['titre' => 'Challenge 2018', 'annee' => 2018, 'url' => '/imagesCHALLENGE_2018/'],
        ['titre' => 'Master 2025', 'annee' => 2025, 'url' => '/imagesMASTER_2025/'],
        ['titre' => 'Master 2024', 'annee' => 2024, 'url' => '/imagesMASTER_2024/'],
        ['titre' => 'Master 2023', 'annee' => 2023, 'url' => '/imagesMASTER_2023/'],
        ['titre' => 'Master 2022', 'annee' => 2022, 'url' => '/imagesMASTER_2022/'],
        ['titre' => 'Master 2020', 'annee' => 2020, 'url' => '/imagesMASTER_2020/'],
        ['titre' => 'Master 2019', 'annee' => 2019, 'url' => '/imagesMASTER_2019/'],
        ['titre' => 'Master 2018', 'annee' => 2018, 'url' => '/imagesMASTER_2018/'],
        ['titre' => 'Master 2017', 'annee' => 2017, 'url' => '/imagesMASTER_2017/'],
        ['titre' => 'Tournoi 2025', 'annee' => 2025, 'url' => '/imagesTOURNOI_2025/'],
        ['titre' => 'Tournoi 2024', 'annee' => 2024, 'url' => '/imagesTOURNOI_2024/'],
        ['titre' => 'Tournoi 2022', 'annee' => 2022, 'url' => '/imagesTOURNOI_2022/'],
        ['titre' => 'Tournoi 2020', 'annee' => 2020, 'url' => '/imagesTOURNOI_2020/'],
        ['titre' => 'Tournoi 2019', 'annee' => 2019, 'url' => '/imagesTOURNOI_2019/'],
        ['titre' => 'Tournoi 2018', 'annee' => 2018, 'url' => '/imagesTOURNOI_2018/'],
        ['titre' => 'Tournoi 2017', 'annee' => 2017, 'url' => '/imagesTOURNOI_2017/'],
        ['titre' => 'Tournoi 2016', 'annee' => 2016, 'url' => '/imagesTOURNOI_2016/'],
        ['titre' => 'Trophée 2026', 'annee' => 2026, 'url' => '/imagesTROPHEE_2026/'],
        ['titre' => 'Trophée 2025', 'annee' => 2025, 'url' => '/imagesTROPHEE_2025/'],
        ['titre' => 'Trophée 2024', 'annee' => 2024, 'url' => '/imagesTROPHEE_2024/'],
        ['titre' => 'Trophée 2023', 'annee' => 2023, 'url' => '/imagesTROPHEE_2023/'],
        ['titre' => 'Trophée 2022', 'annee' => 2022, 'url' => '/imagesTROPHEE_2022/'],
        ['titre' => 'Trophée 2020', 'annee' => 2020, 'url' => '/imagesTROPHEE_2020/'],
        ['titre' => 'Trophée 2019', 'annee' => 2019, 'url' => '/imagesTROPHEE_2019/'],
        ['titre' => 'Trophée 2018', 'annee' => 2018, 'url' => '/imagesTROPHEE_2018/'],
        ['titre' => 'Trophée 2017', 'annee' => 2017, 'url' => '/imagesTROPHEE_2017/'],
        ['titre' => 'Trophée 2016', 'annee' => 2016, 'url' => '/imagesTROPHEE_2016/'],
        ['titre' => "35 ans de l'AGCA", 'annee' => null, 'url' => '/imagesanniversaire35ans/'],
    ],
];
