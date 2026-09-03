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

final class PublicPagesTest extends DbTestCase
{
    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $this->app = new App($this->config());
        $saison = $this->app->service(SaisonRepository::class)->creer('2026-27', '2026-09-01', '2027-06-30');
        $m = $this->app->service(SerieRepository::class)->parCode('M')['id'];
        $golfs = $this->app->service(GolfRepository::class);
        $equipes = $this->app->service(EquipeRepository::class);
        $div = $this->app->service(DivisionRepository::class);
        $d = $div->creer($saison, $m, 'DIV2/POULE B', 1);
        $ids = [];
        foreach (['SAINT-MARTIN-2', 'LUBERON-1', 'SALON', 'FREGATE'] as $i => $nom) {
            $ids[] = $equipes->creer(['golf_id' => $golfs->trouverOuCreer($nom), 'serie_id' => $m, 'nom' => $nom]);
            $div->ajouterEquipe($d, $ids[$i], $i + 1);
        }
        $j = $this->app->service(JourneeRepository::class)->creer($saison, $m, 1, 'aller', '2026-09-26');
        $r = $this->app->service(RencontreRepository::class);
        $id = $r->creer($d, $j, $ids[0], $ids[1], '2026-09-26');
        $r->mettreAJourResultat($id, ['statut' => 'enregistree', 'total_pour' => 12, 'total_contre' => 18, 'pts_rencontre_pour' => 1, 'pts_rencontre_contre' => 3, 'bonus_invite' => 1, 'alertes' => ['FEUILLE_INCOMPLETE']]);
        $r->creer($d, $j, $ids[2], $ids[3], '2026-09-26');
    }

    private function get(string $chemin, array $query = []): string
    {
        $rep = $this->app->executer(new Request('GET', $chemin, $query, [], '127.0.0.1'));
        self::assertSame(200, $rep->statut, $chemin);
        return $rep->corps;
    }

    public function testClassements(): void
    {
        $html = $this->get('/serie/M/classements');
        self::assertStringContainsString('DIV2/POULE B', $html);
        self::assertStringContainsString('LUBERON-1', $html);
        // LUBERON-1 : 3 + 1 bonus = 4 points, en tête
        self::assertMatchesRegularExpression('/<td class="num">1<\/td>\s*<td>LUBERON-1<\/td>\s*<td class="num">4<\/td>/', $html);
        self::assertStringContainsString('2026-27', $html);
    }

    public function testSuiviAvecPastille(): void
    {
        $html = $this->get('/serie/M/suivi');
        self::assertStringContainsString('sam. 26 sept. 2026', $html);
        self::assertStringContainsString('12 - 18', $html);
        self::assertStringContainsString('class="pastille"', $html);
        self::assertStringContainsString('SALON', $html);
    }

    public function testFeuilleViergeEtReglement(): void
    {
        self::assertStringContainsString('DOUBLE', $this->get('/serie/M/feuille-vierge'));
        self::assertStringContainsString('Homme 1re série', $this->get('/serie/H1/reglement'));
        self::assertStringContainsString('Homme 1re série', $this->get('/'));
    }

    public function testSerieInconnue404(): void
    {
        self::assertSame(404, $this->app->executer(new Request('GET', '/serie/X/classements', [], [], ''))->statut);
    }
}
