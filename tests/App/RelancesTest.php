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
use Agca\App\Service\Relances;

final class RelancesTest extends DbTestCase
{
    private App $app; private int $r1; private int $r2; private int $r3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new App($this->config());
        $saison = $this->app->service(SaisonRepository::class)->creer('2026-27', '2026-09-01', '2027-06-30');
        $m = $this->app->service(SerieRepository::class)->parCode('M')['id'];
        $golfs = $this->app->service(GolfRepository::class); $equipes = $this->app->service(EquipeRepository::class);
        $a = $equipes->creer(['golf_id' => $golfs->trouverOuCreer('A'), 'serie_id' => $m, 'nom' => 'A', 'capitaine_email' => 'a@test']);
        $b = $equipes->creer(['golf_id' => $golfs->trouverOuCreer('B'), 'serie_id' => $m, 'nom' => 'B', 'capitaine_email' => 'b@test']);
        $d = $this->app->service(DivisionRepository::class)->creer($saison, $m, 'D', 1);
        $j = $this->app->service(JourneeRepository::class);
        $rep = $this->app->service(RencontreRepository::class);
        $this->r1 = $rep->creer($d, $j->creer($saison, $m, 1, 'aller', '2026-09-26'), $a, $b, '2026-09-26'); // jouée il y a 3 jours
        $this->r2 = $rep->creer($d, $j->creer($saison, $m, 2, 'aller', '2026-09-28'), $b, $a, '2026-09-28'); // jouée hier
        $this->r3 = $rep->creer($d, $j->creer($saison, $m, 3, 'aller', '2026-09-20'), $a, $b, '2026-09-20'); // enregistrée
        $rep->mettreAJourResultat($this->r3, ['statut' => 'enregistree']);
    }

    public function testRelanceSeulementLesRetardsPuisTousLes2Jours(): void
    {
        $svc = $this->app->service(Relances::class);
        $r = $svc->executer(new \DateTimeImmutable('2026-09-29 08:00:00'));
        self::assertSame(['envoyees' => 1, 'rencontres' => [$this->r1]], $r);
        self::assertSame(0, $svc->executer(new \DateTimeImmutable('2026-09-29 20:00:00'))['envoyees']);
        // le lendemain : r2 a maintenant 48 h, r1 relancée il y a 1 jour seulement
        self::assertSame([$this->r2], $svc->executer(new \DateTimeImmutable('2026-09-30 08:00:00'))['rencontres']);
    }

    public function testArretQuandLaFeuilleEstSaisie(): void
    {
        $svc = $this->app->service(Relances::class);
        $svc->executer(new \DateTimeImmutable('2026-09-29 08:00:00'));
        $this->app->service(RencontreRepository::class)->mettreAJourResultat($this->r1, ['statut' => 'enregistree']);
        self::assertSame(1, $svc->executer(new \DateTimeImmutable('2026-10-02 08:00:00'))['envoyees']); // seulement r2
    }
}
