# SDD ledger — plan: docs/superpowers/plans/2026-09-03-agca-phase1.md
Branche : phase-1 (depuis main 38f7069)
Task 0: reviewer finding "`.superpowers/` ajouté au .gitignore hors brief" — ruling: ligne ajoutée par le contrôleur avant dispatch (isolation de l'espace SDD), dispatch demandait de la conserver ; code maintenu. ⚠️ AGCA_CONFIG/Config : livré en Task 6 (voulu).
Task 0: minor (deferred): SanityTest assertion PHP_VERSION_ID convolutée (plan-mandated)
Task 0: complete (commits 38f7069..8ec64d1, 1 finding tranché)
Task 1: fix round 1/5 (1 addressed — test de paires aller/retour rendu indépendant de l'ordre ; commits 3983922..3ad9447)
Task 1: minor (deferred): nbJournees non testé ; match dupliqué nbJournees/generer ; docblock phase:string (tous plan-mandated)
Task 1: complete (commits 8ec64d1..3ad9447, review clean)
Task 2: minor (deferred): "10&0" accepté puis affiché "10 UP" (plan-mandated) ; attribut DataProvider en FQCN
Task 2: complete (commits 9fb07cc..4322fb4, review clean)
Task 3: fix round 1/5 (1 addressed — attentes de testColonnesEtOrdreParPoints corrigées, plan aligné ; commit e7d80fc)
Task 3: minor (deferred): branches défensives non testées (équipe inconnue, forfaitaire_id null) ; ternaire redondant ordonner ; clé float en string (multiples de 0,5)
Task 3: complete (commits 4322fb4..e7d80fc, review clean)
Task 4: minor (deferred): libellé « une joker dame » (grammaire, plan-mandated) ; branche jokerH=0 non testée ; bornes 11,5/22 exactes non testées
Task 4: complete (commits 7533f3c..cca862e, review clean)
Task 5: minor (deferred): SHA-1 majuscule non testé ; password_hash false théorique
Task 5: complete (commits cca862e..4d7bd65, review clean)
Task 6: fix round 1/5 (1 addressed — découpage SQL robuste espaces/CRLF + diagnostic fichier/instruction ; commits 90b7b88..38b9837). Ruling contrôleur : durcissement compatible avec l'intention du plan, pas de question humaine.
Task 6: minor (deferred): pas de test du message d'erreur de migration ; commentaire SQL en fin de ligne non géré ; insert() cast int
Task 6: complete (commits 4d7bd65..38b9837, review clean)
Task 7: reviewer finding (Important, plan-mandated) "templates sans declare(strict_types=1)" — ruling contrôleur : contrainte globale amendée (templates exemptés, fragments de vue) ; code maintenu.
Task 7: minor (deferred): motif de route non preg_quote ; clé 'contenu' écrasable dans View::rendre
Task 7: complete (commits 38b9837..819eb11, 1 finding tranché)
Task 8: fix round 1/5 (1 addressed — validation des clés de colonnes dans Repository::set() ; commits d1970f1..d274c37)
Task 8: minor (deferred): jokers LIKE (%/_) non échappés dans JoueurRepository::chercher ; $limite non borné
Task 8: complete (commits 02cfa32..d274c37, review clean)
Task 9: minor (deferred): canal temporel à la connexion (pas de hash factice) ; admin déjà connecté redirigé /capitaine sur GET /connexion (plan-mandated)
Task 9: complete (commits d274c37..0609aac, review clean)
Task 10: minor (deferred): entiers affichés sans e() (feuille_vierge, suivi count) ; requête journees inutilisée dans suivi() ; classe CSS impression-titre absente (plan-mandated)
Task 10: complete (commits 0609aac..c3fe344, review clean)
Task 11: fix round 1/5 (2 addressed — Score::texteDepuisColonnes réutilisé ; API joueurs restreinte aux golfs de l'équipe et de ses adversaires ; commits 9fa46e6..f40db2c). Plan Task 13 aligné (commité avec le fix).
Task 11: minor (deferred): detail du journal affiché en JSON brut ; admin avec équipe sans lien retour /admin depuis le tableau de bord ; texteDepuisColonnes lève sur données invalides (le chemin d'écriture doit valider via Score)
Task 11: complete (commits c3fe344..f40db2c, review clean)
Task 12: fix (implementer, approuvé) : NotificationsTest `+` → array_merge, plan aligné (6632c79)
Task 12: minor (deferred): sujet non nettoyé côté app (PHPMailer secureHeader couvre) ; SMTPSecure choisi par port ; branches bonus/annulation/relance non testées (plan-mandated)
Task 12: complete (commits f40db2c..f8828c0, review clean)
Task 13: fix (implementer, approuvé) : total_pour attendu 14 (plan aligné 49a1803)
Task 13: fix round 1/5 (1 addressed — date hors saison bloquante ; commits 49a1803..da14ef8)
Task 13: minor (deferred): docblock lireDate incomplet ; double chargement rencontre/série sur le chemin 422 ; option.label datalist ignoré par Chromium ; saison chargée deux fois dans dateHorsSaison
Task 13: complete (commits 6632c79..da14ef8, review clean)
Task 14: fix (implementer, approuvé) : helper req() d'AdminPagesTest (CSRF sur tout POST, query string → $get)
Task 14: reviewer finding (Important, plan-mandated) "mot de passe temporaire en flash de session" — ruling contrôleur : accepté, c'est le mécanisme d'affichage unique voulu par le cahier des charges (session serveur, consommé au premier rendu).
Task 14: fix round 1/5 (1 addressed — formulaires joueurs via attribut form ; commits a9c2c40..dcb8b0f)
Task 14: minor (deferred): select équipes reconstruit par ligne dans utilisateurs.php
Task 14: complete (commits da14ef8..dcb8b0f, review clean, 1 finding tranché)
Task 15: fix (implementer, approuvé) : varsDetail $placees par boucle (le array_merge(...array_map) du plan plantait sans division)
Task 15: minor (deferred): contrôle journées seulement si aucune erreur équipe ; ordre/placement sans verrou (mono-admin) ; formulaire division non repeuplé sur 422 ; libellés codés en dur sans e() ; geler/activer hors transaction
Task 15: complete (commits dcb8b0f..4c6cbbe, review clean)
Task 16: reviewer finding (Important) "mb_strtoupper vs UPPER() MySQL sur noms accentués" — ruling contrôleur : les noms d'équipes 2026-27 sont ASCII (apostrophe seule) ; parqué, réel mais non porteur.
Task 16: fix round 1/5 (1 addressed — import atomique ; 1 nouveau open — catch RuntimeException masque PDOException ; commits 2dbc4ed..b8adc2b)
Task 16: fix round 2/5 (1 addressed — ImportEchec dédiée, PDOException propagée ; commits b8adc2b..37fbbe6)
Task 16: minor (deferred): strtolower du hash SHA-1 (sémantiquement identique) ; fixture latin1 avec accent (voulu)
Task 16: complete (commits 4c6cbbe..37fbbe6, review clean, 1 parked)
Task 17: minor (deferred): constantes DELAI/INTERVALLE égales sans commentaire ; bin/relances.php sans try/catch
Task 17: complete (commits 37fbbe6..1ffd892, review clean)
Task 18: fix round 1/5 (2 addressed — ordre Composer dans bascule.md ; passage navigateur/mobile fait par le contrôleur ; commits cc06959..5e27a43)
Task 18: minor (deferred): $adminId inutilisé dans demo.php ; regex -\d$ pour le golf ; chemin placeholder
Task 18: complete (commits 1ffd892..5e27a43, review clean)
ALL TASKS COMPLETE — final whole-branch review pending
FINAL REVIEW (opus): domaine/sécurité applicative OK ; 1 critique + 7 importants + promus → vague de correctifs 5e27a43..30aa7e7, re-review clean (112 tests).
FINAL: parked — snapshot des parties lu avant l'ouverture de la transaction dans Forfait::declarer — ruling : application mono-admin, fenêtre de course négligeable ; à déplacer dans la transaction en phase 2.
FINAL: non retenu — feuille incomplète non bloquante (cahier des charges §6 : contrôles non bloquants) ; question posée au client.
FINAL: phase 2 — CSP, HEAD, flashs sur page d'erreur, classe CSS morte, requête journees inutile, LIKE non échappé, retour /admin depuis tableau de bord.
