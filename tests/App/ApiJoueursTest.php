<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Http\Request;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;

final class ApiJoueursTest extends DbTestCase
{
    private App $app;
    private int $golfA;
    private int $golfB;
    private int $golfC;
    private int $capitaineA;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $this->app = new App($this->config());
        $saison = $this->app->service(SaisonRepository::class)->creer('2026-27', '2026-09-01', '2027-06-30');
        $h1 = $this->app->service(SerieRepository::class)->parCode('H1')['id'];
        $golfs = $this->app->service(GolfRepository::class);
        $equipes = $this->app->service(EquipeRepository::class);
        $u = $this->app->service(UtilisateurRepository::class);

        $this->golfA = $golfs->trouverOuCreer('VALGARDE');
        $this->golfB = $golfs->trouverOuCreer('SALON');
        $this->golfC = $golfs->trouverOuCreer('ORANGE');

        $equipeA = $equipes->creer(['golf_id' => $this->golfA, 'serie_id' => $h1, 'nom' => 'VALGARDE-1']);
        $equipeB = $equipes->creer(['golf_id' => $this->golfB, 'serie_id' => $h1, 'nom' => 'SALON-1']);

        $this->capitaineA = $u->creer(['identifiant' => 'VALGARDE-1', 'serie_id' => $h1, 'equipe_id' => $equipeA, 'hash_sha1' => sha1('x')]);

        $d = $this->app->service(DivisionRepository::class)->creer($saison, $h1, 'DIVISION 1', 1);
        $j = $this->app->service(JourneeRepository::class)->creer($saison, $h1, 1, 'aller', '2026-10-24');
        $this->app->service(RencontreRepository::class)->creer($d, $j, $equipeA, $equipeB, '2026-10-24');
    }

    private function connecterCapitaineA(): void
    {
        $this->app->session()->demarrer();
        $_SESSION['utilisateur_id'] = $this->capitaineA;
    }

    public function testCapitaineAccedeAuGolfDeSaPropreEquipe(): void
    {
        $this->connecterCapitaineA();
        $rep = $this->app->executer(new Request('GET', '/api/joueurs', ['golf' => (string) $this->golfA], [], '127.0.0.1'));
        self::assertSame(200, $rep->statut);
        self::assertIsArray(json_decode($rep->corps, true));
    }

    public function testCapitaineAccedeAuGolfDeLAdversaireDeLaSaison(): void
    {
        $this->connecterCapitaineA();
        $rep = $this->app->executer(new Request('GET', '/api/joueurs', ['golf' => (string) $this->golfB], [], '127.0.0.1'));
        self::assertSame(200, $rep->statut);
        self::assertIsArray(json_decode($rep->corps, true));
    }

    public function testCapitaineRefuseSurUnGolfEtranger(): void
    {
        $this->connecterCapitaineA();
        $rep = $this->app->executer(new Request('GET', '/api/joueurs', ['golf' => (string) $this->golfC], [], '127.0.0.1'));
        self::assertSame(403, $rep->statut);
        self::assertSame([], json_decode($rep->corps, true));
    }

    public function testAnonymeRedirigeVersConnexion(): void
    {
        $rep = $this->app->executer(new Request('GET', '/api/joueurs', ['golf' => (string) $this->golfA], [], '127.0.0.1'));
        self::assertSame(302, $rep->statut);
    }
}
