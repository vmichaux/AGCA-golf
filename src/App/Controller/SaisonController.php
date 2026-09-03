<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Service\CreationSaison;

final class SaisonController extends Controller
{
    public function liste(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/saisons', ['titre' => 'Saisons', 'saisons' => $this->app->service(SaisonRepository::class)->toutes(), 'series' => $this->app->service(SerieRepository::class)->toutes(), 'erreurs' => [], 'post' => []]);
    }

    public function creer(Request $req): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $dates = [];
        foreach ($this->app->service(SerieRepository::class)->toutes() as $s) {
            $dates[$s['code']] = ['aller' => (array) $req->post('aller_' . $s['code'], []), 'retour' => (array) $req->post('retour_' . $s['code'], [])];
        }
        $r = $this->app->service(CreationSaison::class)->creerSaison((string) $req->post('libelle', ''), (string) $req->post('date_debut', ''), (string) $req->post('date_fin', ''), $dates, $u);
        if ($r['erreurs'] !== []) {
            return $this->rendre('admin/saisons', ['titre' => 'Saisons', 'saisons' => $this->app->service(SaisonRepository::class)->toutes(), 'series' => $this->app->service(SerieRepository::class)->toutes(), 'erreurs' => $r['erreurs'], 'post' => $req->tousPost()], 422);
        }
        $this->flash('succes', 'Saison créée et activée. Ajoutez maintenant les divisions.');
        return $this->rediriger('/admin/saisons/' . $r['id']);
    }

    public function detail(Request $req, string $id): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/saison', $this->varsDetail((int) $id, []));
    }

    public function ajouterDivision(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $equipes = [];
        foreach ((array) $req->post('equipe', []) as $pos => $e) { if ((int) $e > 0) { $equipes[(int) $pos] = (int) $e; } }
        $r = $this->app->service(CreationSaison::class)->ajouterDivision((int) $id, (int) $req->post('serie_id', 0), (string) $req->post('libelle', ''), $equipes, $u);
        if ($r['erreurs'] !== []) { return $this->rendre('admin/saison', $this->varsDetail((int) $id, $r['erreurs']), 422); }
        $this->flash('succes', 'Division créée, rencontres générées.');
        return $this->rediriger('/admin/saisons/' . (int) $id);
    }

    public function geler(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $this->app->service(CreationSaison::class)->geler((int) $id, $u);
        $this->flash('succes', 'Saison gelée : plus aucune saisie possible.');
        return $this->rediriger('/admin/saisons');
    }

    public function activer(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $this->app->service(CreationSaison::class)->activer((int) $id, $u);
        $this->flash('succes', 'Saison activée.');
        return $this->rediriger('/admin/saisons');
    }

    private function varsDetail(int $id, array $erreurs): array
    {
        $saison = $this->app->service(SaisonRepository::class)->parId($id) ?? $this->introuvable();
        $series = [];
        foreach ($this->app->service(SerieRepository::class)->toutes() as $s) {
            $divs = $this->app->service(DivisionRepository::class)->parSaisonEtSerie($id, (int) $s['id']);
            foreach ($divs as &$d) { $d['equipes'] = $this->app->service(DivisionRepository::class)->equipes((int) $d['id']); }
            unset($d);
            $placees = [];
            foreach ($divs as $d) { foreach ($d['equipes'] as $e) { $placees[] = $e['equipe_id']; } }
            $series[] = $s + ['divisions' => $divs, 'journees' => $this->app->service(JourneeRepository::class)->parSaisonEtSerie($id, (int) $s['id']),
                'equipes_libres' => array_values(array_filter($this->app->service(EquipeRepository::class)->parSerie((int) $s['id']), fn($e) => !in_array((int) $e['id'], array_map('intval', $placees), true)))];
        }
        return ['titre' => 'Saison ' . $saison['libelle'], 'saison' => $saison, 'series' => $series, 'erreurs' => $erreurs];
    }
}
