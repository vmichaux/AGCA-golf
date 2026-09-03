<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Http\HttpException;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\PartieRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Service\ClassementService;
use Agca\App\Service\Forfait;
use Agca\App\Service\Mailer;

final class ForfaitTest extends DbTestCase
{
    private App $app; private int $rencontre; private int $division; private int $rec; private int $inv;
    private array $admin = ['id' => 1, 'identifiant' => 'ADMIN', 'est_admin' => 1, 'equipe_id' => null];

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new App($this->config());
        $saison = $this->app->service(SaisonRepository::class)->creer('2026-27', '2026-09-01', '2027-06-30');
        $m = $this->app->service(SerieRepository::class)->parCode('M')['id'];
        $golfs = $this->app->service(GolfRepository::class); $equipes = $this->app->service(EquipeRepository::class);
        $this->rec = $equipes->creer(['golf_id' => $golfs->trouverOuCreer('SALON'), 'serie_id' => $m, 'nom' => 'SALON', 'capitaine_email' => 'salon@test']);
        $this->inv = $equipes->creer(['golf_id' => $golfs->trouverOuCreer('FREGATE'), 'serie_id' => $m, 'nom' => 'FREGATE', 'capitaine_email' => 'fregate@test']);
        $div = $this->app->service(DivisionRepository::class);
        $this->division = $div->creer($saison, $m, 'DIV2/POULE B', 1);
        $div->ajouterEquipe($this->division, $this->rec, 1); $div->ajouterEquipe($this->division, $this->inv, 2);
        $j = $this->app->service(JourneeRepository::class)->creer($saison, $m, 1, 'aller', '2026-09-26');
        $this->rencontre = $this->app->service(RencontreRepository::class)->creer($this->division, $j, $this->rec, $this->inv, '2026-09-26');
    }

    public function testForfaitDuRecevantDonne4PointsALInvite(): void
    {
        $this->app->service(Forfait::class)->declarer($this->rencontre, 'recevant', $this->admin);
        $r = $this->app->service(RencontreRepository::class)->parId($this->rencontre);
        self::assertSame(['forfait', $this->rec, 0, 15, '0.0', '3.0', '1.0'], [$r['statut'], (int) $r['forfaitaire_id'], (int) $r['total_pour'], (int) $r['total_contre'], $r['pts_rencontre_pour'], $r['pts_rencontre_contre'], $r['bonus_invite']]);
        $cl = $this->app->service(ClassementService::class)->pourDivision($this->division);
        self::assertSame(['FREGATE', 4.0, 1], [$cl[0]['nom'], $cl[0]['points'], $cl[0]['joues']]);
        self::assertSame(['SALON', 0.0, 1], [$cl[1]['nom'], $cl[1]['points'], $cl[1]['forfaits']]);
        self::assertSame('salon@test', $this->app->service(Mailer::class)->dernierEnvoi()['a']);
        self::assertContains('fregate@test', $this->app->service(Mailer::class)->dernierEnvoi()['cc']);
    }

    public function testForfaitDeLInviteSansBonus(): void
    {
        $this->app->service(Forfait::class)->declarer($this->rencontre, 'invite', $this->admin);
        $r = $this->app->service(RencontreRepository::class)->parId($this->rencontre);
        self::assertSame([15, 0, '3.0', '0.0', '0.0'], [(int) $r['total_pour'], (int) $r['total_contre'], $r['pts_rencontre_pour'], $r['pts_rencontre_contre'], $r['bonus_invite']]);
    }

    public function testAnnulationRemetAJouer(): void
    {
        $f = $this->app->service(Forfait::class);
        $f->declarer($this->rencontre, 'invite', $this->admin);
        $f->annuler($this->rencontre, $this->admin);
        $r = $this->app->service(RencontreRepository::class)->parId($this->rencontre);
        self::assertSame(['a_jouer', null, 0, 0], [$r['statut'], $r['forfaitaire_id'], (int) $r['total_pour'], (int) $r['pts_rencontre_pour']]);
        self::assertSame([], $this->app->service(PartieRepository::class)->parRencontre($this->rencontre));
    }

    public function testNonAdminRefuse(): void
    {
        $this->expectException(HttpException::class);
        $this->app->service(Forfait::class)->declarer($this->rencontre, 'invite', ['id' => 2, 'identifiant' => 'SALON', 'est_admin' => 0, 'equipe_id' => $this->rec]);
    }
}
