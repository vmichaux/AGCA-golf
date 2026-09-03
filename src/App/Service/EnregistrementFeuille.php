<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Http\HttpException;
use Agca\App\Repository\JoueurRepository;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\PartieRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\Domain\CalculRencontre;
use Agca\Domain\Controles;
use Agca\Domain\Score;
use Agca\Domain\Serie;

final class EnregistrementFeuille
{
    public function __construct(private App $app) {}

    /** @return list<array{numero:int, type:string}> */
    public function structure(Serie $serie): array
    {
        $out = [];
        foreach ($serie->structure as $i => $t) { $out[] = ['numero' => $i + 1, 'type' => $t === 'D' ? 'double' : 'simple']; }
        return $out;
    }

    /** @return array{date_reelle:?string, parties:list<array>, erreurs:list<string>} */
    public function lire(Serie $serie, array $post): array
    {
        $erreurs = [];
        $date = self::lireDate((string) ($post['date_reelle'] ?? ''));
        if ($date === null) { $erreurs[] = 'Date du match illisible (attendu : jour/mois/année).'; }
        $parties = [];
        foreach ($this->structure($serie) as $s) {
            $n = $s['numero']; $src = $post['p'][$n] ?? [];
            $nb = $s['type'] === 'double' ? 2 : 1;
            $partie = ['numero' => $n, 'type' => $s['type'], 'rec' => [], 'inv' => [], 'resultat' => null, 'score' => null, 'score_texte' => trim((string) ($src['score'] ?? ''))];
            foreach (['rec', 'inv'] as $camp) {
                for ($k = 1; $k <= $nb; $k++) {
                    $nom = trim((string) ($src["{$camp}{$k}_nom"] ?? ''));
                    $indexTexte = trim((string) ($src["{$camp}{$k}_index"] ?? ''));
                    $index = null;
                    if ($indexTexte !== '') {
                        $norm = str_replace(',', '.', $indexTexte);
                        if (!is_numeric($norm) || (float) $norm < 0 || (float) $norm > 54) { $erreurs[] = "Partie $n : index illisible « $indexTexte »."; }
                        else { $index = round((float) $norm, 1); }
                    }
                    $sexe = strtoupper(trim((string) ($src["{$camp}{$k}_sexe"] ?? '')));
                    $partie[$camp][] = ['nom' => $nom, 'index' => $index, 'sexe' => in_array($sexe, ['H', 'D'], true) ? $sexe : null];
                }
            }
            $res = strtoupper(trim((string) ($src['resultat'] ?? '')));
            if ($res !== '' && !in_array($res, ['G', 'N', 'P'], true)) { $erreurs[] = "Partie $n : résultat inconnu."; }
            else { $partie['resultat'] = $res === '' ? null : $res; }
            try { $partie['score'] = Score::depuisTexte($partie['score_texte']); }
            catch (\InvalidArgumentException) { $erreurs[] = "Partie $n : score illisible « {$partie['score_texte']} » (exemples : 3&2, 1UP, AS)."; }
            $parties[] = $partie;
        }
        return ['date_reelle' => $date, 'parties' => $parties, 'erreurs' => $erreurs];
    }

    /** @return array{erreurs:list<string>, alertes:list<string>} */
    public function enregistrer(int $rencontreId, array $post, array $utilisateur): array
    {
        $acces = $this->app->service(AccesRencontre::class);
        $r = $acces->charger($rencontreId, $utilisateur);
        if (!$acces->peutSaisir($r, $utilisateur)) { throw new HttpException(403, 'Cette feuille ne peut plus être modifiée. Contactez l\'administrateur.'); }
        $serie = $this->app->service(SerieRepository::class)->parId((int) $r['serie_id'])['serie'];
        $lu = $this->lire($serie, $post);
        if ($lu['date_reelle'] !== null && $this->dateHorsSaison($r, $lu['date_reelle'])) {
            $saison = $this->app->service(SaisonRepository::class)->parId((int) $r['saison_id']);
            $lu['erreurs'][] = 'Date du match hors de la saison (du ' . fmt_date($saison['date_debut']) . ' au ' . fmt_date($saison['date_fin']) . ').';
        }
        if ($lu['erreurs'] !== []) { return ['erreurs' => $lu['erreurs'], 'alertes' => []]; }
        $correction = $r['statut'] !== 'a_jouer';
        $alertes = Controles::verifier($serie, $lu['parties']);
        $calcul = CalculRencontre::calculer($serie, array_column($lu['parties'], 'resultat'));

        $this->app->db()->transaction(function () use ($r, $lu, $calcul, $alertes, $utilisateur, $correction, $rencontreId) {
            $joueurs = $this->app->service(JoueurRepository::class);
            $lignes = [];
            foreach ($lu['parties'] as $i => $p) {
                $l = ['numero' => $p['numero'], 'type' => $p['type'], 'resultat' => $p['resultat'],
                    'score_trous' => $p['score'] === null || $p['score']->as ? null : $p['score']->trous,
                    'score_restants' => $p['score'] === null || $p['score']->as ? null : $p['score']->restants,
                    'score_as' => $p['score'] !== null && $p['score']->as ? 1 : 0,
                    'pts_pour' => $calcul->ptsParties[$i][0], 'pts_contre' => $calcul->ptsParties[$i][1]];
                foreach (['rec' => (int) $r['recevant_golf_id'], 'inv' => (int) $r['invite_golf_id']] as $camp => $golf) {
                    foreach ([1, 2] as $k) {
                        $j = $p[$camp][$k - 1] ?? null;
                        $l["{$camp}_joueur{$k}_id"] = $j !== null && $j['nom'] !== '' ? $joueurs->trouverOuCreer($golf, $j['nom'], $j['sexe'], $j['index'], (int) $utilisateur['id']) : null;
                        $l["{$camp}_index{$k}"] = $j['index'] ?? null;
                        $l["{$camp}_sexe{$k}"] = $j['sexe'] ?? null;
                    }
                }
                $lignes[] = $l;
            }
            $this->app->service(PartieRepository::class)->remplacer($rencontreId, $lignes);
            $rencontres = $this->app->service(RencontreRepository::class);
            $rencontres->mettreAJourDate($rencontreId, $lu['date_reelle'], $lu['date_reelle'] !== $r['date_calendrier']);
            $rencontres->mettreAJourResultat($rencontreId, ['statut' => 'enregistree', 'forfaitaire_id' => null,
                'total_pour' => $calcul->totalPour, 'total_contre' => $calcul->totalContre,
                'pts_rencontre_pour' => $calcul->ptsPour, 'pts_rencontre_contre' => $calcul->ptsContre, 'bonus_invite' => $calcul->bonusInvite,
                'alertes' => $alertes, 'alertes_vues' => 0, 'enregistree_le' => date('Y-m-d H:i:s'), 'enregistree_par' => (int) $utilisateur['id']]);
            $this->app->service(JournalRepository::class)->ecrire((int) $utilisateur['id'], $correction ? 'feuille_corrigee' : 'feuille_enregistree', 'rencontre', $rencontreId,
                ['total' => $calcul->totalPour . '-' . $calcul->totalContre, 'alertes' => $alertes]);
        });
        $this->app->service(Notifications::class)->feuilleEnregistree($this->app->service(RencontreRepository::class)->parId($rencontreId), $correction, $utilisateur);
        return ['erreurs' => [], 'alertes' => $alertes];
    }

    /** @return string|null message d'erreur */
    public function changerDate(int $rencontreId, string $date, array $utilisateur): ?string
    {
        $acces = $this->app->service(AccesRencontre::class);
        $r = $acces->charger($rencontreId, $utilisateur);
        if (!$acces->peutModifierDate($r, $utilisateur)) { return 'La date ne peut plus être modifiée. Contactez l\'administrateur.'; }
        $d = self::lireDate($date);
        if ($d === null) { return 'Date illisible (attendu : jour/mois/année).'; }
        if ($this->dateHorsSaison($r, $d)) {
            $saison = $this->app->service(SaisonRepository::class)->parId((int) $r['saison_id']);
            return 'Date du match hors de la saison (du ' . fmt_date($saison['date_debut']) . ' au ' . fmt_date($saison['date_fin']) . ').';
        }
        if ($d === $r['date_reelle']) { return null; }
        $this->app->service(RencontreRepository::class)->mettreAJourDate($rencontreId, $d, $d !== $r['date_calendrier']);
        $this->app->service(JournalRepository::class)->ecrire((int) $utilisateur['id'], 'report', 'rencontre', $rencontreId, ['de' => $r['date_reelle'], 'a' => $d]);
        $this->app->service(Notifications::class)->report($this->app->service(RencontreRepository::class)->parId($rencontreId), $r['date_reelle'], $utilisateur);
        return null;
    }

    private function dateHorsSaison(array $r, string $ymd): bool
    {
        $saison = $this->app->service(SaisonRepository::class)->parId((int) $r['saison_id']);
        return $ymd < $saison['date_debut'] || $ymd > $saison['date_fin'];
    }

    /** Accepte Y-m-d, d/m/Y, d-m-Y ; retourne Y-m-d ou null. */
    public static function lireDate(string $texte): ?string
    {
        $t = trim($texte);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y'] as $f) {
            $d = \DateTimeImmutable::createFromFormat('!' . $f, $t);
            if ($d !== false && $d->format($f) === $t) { return $d->format('Y-m-d'); }
        }
        return null;
    }
}
