<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Service\ClassementService;
use Agca\App\Service\Saisons;

final class PublicController extends Controller
{
    public function accueil(Request $req): Response
    {
        return $this->rendre('public/accueil', ['titre' => 'AGCA — Interclubs', 'series' => $this->app->service(SerieRepository::class)->toutes(),
            'saison' => $this->app->service(Saisons::class)->courante(null)]);
    }

    public function classements(Request $req, string $code): Response
    {
        $ctx = $this->contexte($req, $code);
        foreach ($ctx['divisions'] as &$d) { $d['classement'] = $this->app->service(ClassementService::class)->pourDivision((int) $d['id']); }
        unset($d);
        return $this->rendre('public/classements', $ctx + ['titre' => 'Classements ' . $ctx['serie']['libelle']]);
    }

    public function suivi(Request $req, string $code): Response
    {
        $ctx = $this->contexte($req, $code);
        foreach ($ctx['divisions'] as &$d) { $d['rencontres'] = $this->app->service(RencontreRepository::class)->parDivision((int) $d['id']); }
        unset($d);
        $ctx['journees'] = $ctx['saison'] === null ? [] : $this->app->service(JourneeRepository::class)->parSaisonEtSerie((int) $ctx['saison']['id'], (int) $ctx['serie']['id']);
        return $this->rendre('public/suivi', $ctx + ['titre' => 'Calendrier et suivi ' . $ctx['serie']['libelle']]);
    }

    public function feuilleVierge(Request $req, string $code): Response
    {
        $serie = $this->serie($code);
        return $this->rendre('public/feuille_vierge', ['titre' => 'Feuille de match vierge', 'serie' => $serie], 200);
    }

    public function reglement(Request $req, string $code): Response
    {
        $serie = $this->serie($code);
        return $this->rendre('public/reglement', ['titre' => 'Règlement ' . $serie['libelle'], 'serie' => $serie]);
    }

    private function serie(string $code): array
    {
        return $this->app->service(SerieRepository::class)->parCode($code) ?? $this->introuvable('Série inconnue');
    }

    /** @return array{serie:array, saison:?array, saisons:list, divisions:list} */
    private function contexte(Request $req, string $code): array
    {
        $serie = $this->serie($code);
        $saisons = $this->app->service(Saisons::class);
        $saison = $saisons->courante($req->get('saison'));
        $divisions = $saison === null ? [] : $this->app->service(DivisionRepository::class)->parSaisonEtSerie((int) $saison['id'], (int) $serie['id']);
        return ['serie' => $serie, 'saison' => $saison, 'saisons' => $saisons->toutes(), 'divisions' => $divisions];
    }
}
