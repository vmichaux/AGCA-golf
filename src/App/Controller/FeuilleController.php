<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\PartieRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Service\AccesRencontre;
use Agca\App\Service\EnregistrementFeuille;
use Agca\Domain\Controles;
use Agca\Domain\Score;
use Agca\Domain\Serie;

final class FeuilleController extends Controller
{
    public function lecture(Request $req, string $id): Response
    {
        $u = $this->exigerConnexion();
        $acces = $this->app->service(AccesRencontre::class);
        $r = $acces->charger((int) $id, $u);
        return $this->rendre('feuille/lecture', [
            'titre' => 'Feuille ' . $r['recevant_nom'] . ' – ' . $r['invite_nom'],
            'rencontre' => $r,
            'parties' => $this->app->service(PartieRepository::class)->parRencontre((int) $id),
            'peutSaisir' => $acces->peutSaisir($r, $u),
            'peutModifierDate' => $acces->peutModifierDate($r, $u),
            'journal' => $u['est_admin'] ? $this->app->service(JournalRepository::class)->parCible('rencontre', (int) $id) : [],
        ]);
    }

    public function formulaireSaisie(Request $req, string $id): Response
    {
        $u = $this->exigerConnexion();
        $acces = $this->app->service(AccesRencontre::class);
        $r = $acces->charger((int) $id, $u);
        if (!$acces->peutSaisir($r, $u)) { $this->interdit('Cette feuille ne peut plus être modifiée. Contactez l\'administrateur.'); }
        $serie = $this->app->service(SerieRepository::class)->parId((int) $r['serie_id'])['serie'];
        return $this->rendre('feuille/saisie', $this->varsSaisie($r, $serie, $this->valeursExistantes((int) $id, $serie), []));
    }

    public function enregistrer(Request $req, string $id): Response
    {
        $u = $this->exigerConnexion();
        $this->exigerCsrf($req);
        $svc = $this->app->service(EnregistrementFeuille::class);
        $res = $svc->enregistrer((int) $id, $req->tousPost(), $u);
        if ($res['erreurs'] !== []) {
            $r = $this->app->service(AccesRencontre::class)->charger((int) $id, $u);
            $serie = $this->app->service(SerieRepository::class)->parId((int) $r['serie_id'])['serie'];
            return $this->rendre('feuille/saisie', $this->varsSaisie($r, $serie, $req->tousPost(), $res['erreurs']), 422);
        }
        $this->flash('succes', 'Feuille enregistrée et comptée au classement.' . ($res['alertes'] !== [] ? ' Points à vérifier signalés à l\'administrateur : ' . implode(', ', array_map(fn($c) => Controles::LIBELLES[$c], $res['alertes'])) . '.' : ''));
        return $this->rediriger('/rencontre/' . (int) $id);
    }

    public function changerDate(Request $req, string $id): Response
    {
        $u = $this->exigerConnexion();
        $this->exigerCsrf($req);
        $erreur = $this->app->service(EnregistrementFeuille::class)->changerDate((int) $id, (string) $req->post('date_reelle', ''), $u);
        $this->flash($erreur === null ? 'succes' : 'erreur', $erreur ?? 'Date mise à jour ; le capitaine adverse et l\'administrateur sont prévenus.');
        return $this->rediriger((string) $req->post('retour', '/rencontre/' . (int) $id));
    }

    /** Pré-remplit le formulaire depuis les parties existantes (correction admin). */
    private function valeursExistantes(int $id, Serie $serie): array
    {
        $r = $this->app->service(RencontreRepository::class)->parId($id);
        $post = ['date_reelle' => $r['date_reelle'], 'p' => []];
        foreach ($this->app->service(PartieRepository::class)->parRencontre($id) as $p) {
            $score = Score::texteDepuisColonnes($p['score_trous'], $p['score_restants'], $p['score_as']);
            $post['p'][(int) $p['numero']] = ['rec1_nom' => $p['rec_nom1'], 'rec1_index' => $p['rec_index1'], 'rec1_sexe' => $p['rec_sexe1'], 'rec2_nom' => $p['rec_nom2'], 'rec2_index' => $p['rec_index2'], 'rec2_sexe' => $p['rec_sexe2'],
                'inv1_nom' => $p['inv_nom1'], 'inv1_index' => $p['inv_index1'], 'inv1_sexe' => $p['inv_sexe1'], 'inv2_nom' => $p['inv_nom2'], 'inv2_index' => $p['inv_index2'], 'inv2_sexe' => $p['inv_sexe2'],
                'resultat' => $p['resultat'], 'score' => $score];
        }
        return $post;
    }

    private function varsSaisie(array $r, Serie $serie, array $post, array $erreurs): array
    {
        return ['titre' => 'Saisie ' . $r['recevant_nom'] . ' – ' . $r['invite_nom'], 'rencontre' => $r, 'serie' => $serie,
            'structure' => $this->app->service(EnregistrementFeuille::class)->structure($serie), 'post' => $post, 'erreurs' => $erreurs,
            'correction' => $r['statut'] !== 'a_jouer'];
    }
}
