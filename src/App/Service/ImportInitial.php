<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;

final class ImportInitial
{
    public function __construct(private App $app) {}

    /** @return array{rapport:list<string>, erreurs:list<string>} */
    public function executer(array $d, array $admin): array
    {
        $rapport = []; $erreurs = [];
        $db = $this->app->db();
        foreach ($this->app->service(SaisonRepository::class)->toutes() as $s) {
            if ($s['libelle'] === $d['saison']['libelle']) { return ['rapport' => [], 'erreurs' => ["La saison {$s['libelle']} existe déjà : import refusé."]]; }
        }
        foreach (['Mequipe', 'H1equipe', 'Midentifiant', 'H1identifiant'] as $t) {
            if ($db->one("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$t]) === null) { $erreurs[] = "Ancienne table absente : $t (charger le dump d'abord)."; }
        }
        if ($erreurs !== []) { return ['rapport' => [], 'erreurs' => $erreurs]; }

        $series = $this->app->service(SerieRepository::class);
        $golfs = $this->app->service(GolfRepository::class);
        $equipes = $this->app->service(EquipeRepository::class);
        $utilisateurs = $this->app->service(UtilisateurRepository::class);
        $anciennes = ['M' => ['equipe' => 'Mequipe', 'ident' => 'Midentifiant'], 'H1' => ['equipe' => 'H1equipe', 'ident' => 'H1identifiant']];
        $adminCree = false;

        $db->transaction(function () use ($d, $db, $series, $golfs, $equipes, $utilisateurs, $anciennes, &$rapport, &$erreurs, &$adminCree) {
            foreach ($anciennes as $code => $tables) {
                $serie = $series->parCode($code); $serieId = (int) $serie['id'];
                $exclues = array_map('mb_strtoupper', $d['exclues'][$code] ?? []);
                $mapGolf = array_change_key_case($d['golfs'][$code] ?? [], CASE_UPPER);
                $idsEquipe = [];
                foreach ($db->all("SELECT * FROM `{$tables['equipe']}` ORDER BY ID") as $e) {
                    $nom = trim($e['nomequipe']);
                    if (in_array(mb_strtoupper($nom), $exclues, true)) { $rapport[] = "$code : $nom ignorée (exclue)."; continue; }
                    if (!isset($mapGolf[mb_strtoupper($nom)])) { $rapport[] = "$code : $nom ignorée (pas dans la liste des golfs 2026-27)."; continue; }
                    $golfId = $golfs->trouverOuCreer($mapGolf[mb_strtoupper($nom)]);
                    if ($equipes->parNomEtSerie($nom, $serieId) !== null) { $rapport[] = "$code : $nom déjà présente."; continue; }
                    $idsEquipe[mb_strtoupper($nom)] = $equipes->creer(['golf_id' => $golfId, 'serie_id' => $serieId, 'nom' => $nom,
                        'capitaine_nom' => trim($e['nomcap']) ?: null, 'capitaine_prenom' => trim($e['prenomcap']) ?: null,
                        'capitaine_email' => trim(explode(' ', trim($e['email']))[0]) ?: null, 'capitaine_tel' => trim($e['telcap']) ?: null]);
                }
                $rapport[] = "$code : " . count($idsEquipe) . ' équipes importées.';
                // Identifiants : le plus grand ID par nom d'équipe fait foi.
                $derniers = [];
                foreach ($db->all("SELECT * FROM `{$tables['ident']}` ORDER BY ID") as $i) { $derniers[mb_strtoupper(trim($i['nomequipe']))] = $i; }
                $n = 0;
                foreach ($derniers as $nomMaj => $i) {
                    if ($nomMaj === 'ADMIN') {
                        if (!$adminCree) { $utilisateurs->creer(['identifiant' => 'ADMIN', 'serie_id' => null, 'hash_sha1' => strtolower(trim($i['pass'])), 'est_admin' => 1, 'nom_affiche' => 'Robert Michaux']); $adminCree = true; $rapport[] = 'ADMIN créé.'; }
                        continue;
                    }
                    if (!isset($idsEquipe[$nomMaj])) { $rapport[] = "$code : identifiant {$i['nomequipe']} ignoré (équipe absente)."; continue; }
                    $utilisateurs->creer(['identifiant' => trim($i['nomequipe']), 'serie_id' => $serieId, 'equipe_id' => $idsEquipe[$nomMaj], 'hash_sha1' => strtolower(trim($i['pass']))]);
                    $n++;
                }
                $rapport[] = "$code : $n identifiants importés.";
            }
        });

        $creation = $this->app->service(CreationSaison::class);
        $saison = $creation->creerSaison($d['saison']['libelle'], $d['saison']['date_debut'], $d['saison']['date_fin'], $d['dates'], $admin + ['est_admin' => 1]);
        if ($saison['erreurs'] !== []) { return ['rapport' => $rapport, 'erreurs' => $saison['erreurs']]; }
        $rapport[] = "Saison {$d['saison']['libelle']} créée.";
        foreach ($d['divisions'] as $code => $divisions) {
            $serieId = (int) $series->parCode($code)['id'];
            foreach ($divisions as [$libelle, $positions]) {
                $ids = [];
                foreach ($positions as $pos => $nom) {
                    $e = $equipes->parNomEtSerie($nom, $serieId);
                    if ($e === null) { $erreurs[] = "$code / $libelle : équipe $nom introuvable."; continue 2; }
                    $ids[(int) $pos] = (int) $e['id'];
                }
                $r = $creation->ajouterDivision((int) $saison['id'], $serieId, $libelle, $ids, $admin + ['est_admin' => 1]);
                if ($r['erreurs'] !== []) { array_push($erreurs, ...$r['erreurs']); continue; }
                $rapport[] = "$code : division $libelle créée (" . count($ids) . ' équipes).';
            }
        }
        foreach ($d['reports'] ?? [] as [$code, $rec, $inv, $phase, $numero, $date]) {
            $serieId = (int) $series->parCode($code)['id'];
            $r = $db->one('SELECT r.id FROM agca_rencontre r JOIN agca_equipe a ON a.id = r.recevant_id JOIN agca_equipe b ON b.id = r.invite_id JOIN agca_journee j ON j.id = r.journee_id
                WHERE a.serie_id = ? AND a.nom = ? AND b.nom = ? AND j.phase = ? AND j.numero = ?', [$serieId, $rec, $inv, $phase, $numero]);
            if ($r === null) { $erreurs[] = "Report $rec – $inv introuvable."; continue; }
            $this->app->service(RencontreRepository::class)->mettreAJourDate((int) $r['id'], $date, true);
            $rapport[] = "Report $rec – $inv au $date.";
        }
        $this->app->service(JournalRepository::class)->ecrire($admin['id'] ?? null, 'import_initial', 'saison', (int) $saison['id'], ['rapport' => $rapport]);
        return ['rapport' => $rapport, 'erreurs' => $erreurs];
    }
}
