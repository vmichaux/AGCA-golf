<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Service\CreationSaison;

final class CreationSaisonTest extends DbTestCase
{
    private App $app; private array $admin = ['id' => 1, 'identifiant' => 'ADMIN', 'est_admin' => 1];
    private const DATES_M = ['aller' => ['2026-09-26', '2026-11-14', '2026-11-28', '2026-12-05', '2026-12-19'], 'retour' => ['2027-01-16', '2027-01-30', '2027-02-13', '2027-02-27', '2027-03-13']];

    protected function setUp(): void { parent::setUp(); $this->app = new App($this->config()); }

    private function equipes(string $code, array $noms): array
    {
        $serie = $this->app->service(SerieRepository::class)->parCode($code)['id'];
        $ids = [];
        foreach ($noms as $n) { $ids[] = $this->app->service(EquipeRepository::class)->creer(['golf_id' => $this->app->service(GolfRepository::class)->trouverOuCreer($n), 'serie_id' => $serie, 'nom' => $n]); }
        return $ids;
    }

    public function testSaisonJourneesEtPouleA4(): void
    {
        $svc = $this->app->service(CreationSaison::class);
        $ancienne = $this->app->service(SaisonRepository::class)->creer('2025-26', '2025-09-01', '2026-06-30');
        $r = $svc->creerSaison('2026-27', '2026-09-01', '2027-06-30', ['M' => self::DATES_M, 'H1' => ['aller' => ['2026-10-24', '2026-11-14', '2026-12-12', '', ''], 'retour' => ['2027-01-23', '2027-02-13', '2027-03-06', '', '']]], $this->admin);
        self::assertSame([], $r['erreurs']);
        self::assertSame('gelee', $this->app->service(SaisonRepository::class)->parId($ancienne)['statut']);
        $m = $this->app->service(SerieRepository::class)->parCode('M')['id']; $h1 = $this->app->service(SerieRepository::class)->parCode('H1')['id'];
        self::assertCount(10, $this->app->service(JourneeRepository::class)->parSaisonEtSerie($r['id'], $m));
        self::assertCount(6, $this->app->service(JourneeRepository::class)->parSaisonEtSerie($r['id'], $h1));

        [$a, $b, $c, $d] = $this->equipes('M', ['SAINT-MARTIN-2', 'LUBERON-1', 'SALON', 'FREGATE']);
        $div = $svc->ajouterDivision($r['id'], $m, 'DIV2/POULE B', [1 => $a, 2 => $b, 3 => $c, 4 => $d], $this->admin);
        self::assertSame([], $div['erreurs']);
        $rencontres = $this->app->service(RencontreRepository::class)->parDivision($div['id']);
        self::assertCount(12, $rencontres);
        self::assertSame(['SAINT-MARTIN-2', 'LUBERON-1', '2026-09-26', 'aller'], [$rencontres[0]['recevant_nom'], $rencontres[0]['invite_nom'], $rencontres[0]['date_reelle'], $rencontres[0]['journee_phase']]);
        self::assertSame(['LUBERON-1', 'SAINT-MARTIN-2', '2027-01-16'], [$rencontres[6]['recevant_nom'], $rencontres[6]['invite_nom'], $rencontres[6]['date_reelle']]);
    }

    public function testPouleA5EtErreurs(): void
    {
        $svc = $this->app->service(CreationSaison::class);
        $r = $svc->creerSaison('2026-27', '2026-09-01', '2027-06-30', ['M' => self::DATES_M, 'H1' => ['aller' => ['2026-10-24', '2026-11-14', '2026-12-12'], 'retour' => ['2027-01-23', '2027-02-13', '2027-03-06']]], $this->admin);
        $m = $this->app->service(SerieRepository::class)->parCode('M')['id']; $h1 = $this->app->service(SerieRepository::class)->parCode('H1')['id'];
        $e = $this->equipes('M', ['SAINT-MARTIN-1', 'VALGARDE-1', 'SAINTE-MAXIME', 'ESTEREL', 'Gde-BASTIDE-1']);
        $div = $svc->ajouterDivision($r['id'], $m, 'DIV à 5 n°1', array_combine([1, 2, 3, 4, 5], $e), $this->admin);
        self::assertSame([], $div['erreurs']);
        $rencontres = $this->app->service(RencontreRepository::class)->parDivision($div['id']);
        self::assertCount(20, $rencontres);
        self::assertSame(['2026-12-19', '2027-03-13'], [$rencontres[9]['date_reelle'], $rencontres[19]['date_reelle']]);
        // équipe déjà placée
        $doublon = $svc->ajouterDivision($r['id'], $m, 'X', [1 => $e[0], 2 => $e[1], 3 => $e[2], 4 => $e[3]], $this->admin);
        self::assertNotSame([], $doublon['erreurs']);
        // H1 : 5 équipes mais seulement 3 journées
        $h = $this->equipes('H1', ['A', 'B', 'C', 'D', 'E']);
        $trop = $svc->ajouterDivision($r['id'], $h1, 'DIVISION 1', array_combine([1, 2, 3, 4, 5], $h), $this->admin);
        self::assertStringContainsString('journée', implode(' ', $trop['erreurs']));
        // positions non contiguës
        $mauvais = $svc->ajouterDivision($r['id'], $h1, 'DIVISION 1', [1 => $h[0], 2 => $h[1], 3 => $h[2], 5 => $h[3]], $this->admin);
        self::assertNotSame([], $mauvais['erreurs']);
        self::assertSame(1, count($this->app->service(DivisionRepository::class)->parSaisonEtSerie($r['id'], $m)));
    }

    public function testGelEtActivation(): void
    {
        $svc = $this->app->service(CreationSaison::class);
        $a = $svc->creerSaison('2025-26', '2025-09-01', '2026-06-30', [], $this->admin)['id'];
        $b = $svc->creerSaison('2026-27', '2026-09-01', '2027-06-30', [], $this->admin)['id'];
        $saisons = $this->app->service(SaisonRepository::class);
        self::assertSame(['gelee', 'active'], [$saisons->parId($a)['statut'], $saisons->parId($b)['statut']]);
        $svc->activer($a, $this->admin);
        self::assertSame(['active', 'gelee'], [$saisons->parId($a)['statut'], $saisons->parId($b)['statut']]);
        $svc->geler($a, $this->admin);
        self::assertNull($saisons->active());
    }
}
