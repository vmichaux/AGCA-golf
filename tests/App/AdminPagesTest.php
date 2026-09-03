<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Http\Request;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JoueurRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;

final class AdminPagesTest extends DbTestCase
{
    private App $app; private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $this->app = new App($this->config());
        $this->app->service(SaisonRepository::class)->creer('2026-27', '2026-09-01', '2027-06-30');
        $u = $this->app->service(UtilisateurRepository::class);
        $this->adminId = $u->creer(['identifiant' => 'ADMIN', 'serie_id' => null, 'hash_sha1' => sha1('x'), 'est_admin' => 1]);
    }

    private function req(string $m, string $chemin, array $post = []): \Agca\App\Http\Response
    {
        if ($m === 'POST') { $post['_csrf'] = $this->app->session()->csrf(); }
        $chemin = (string) $chemin;
        $get = [];
        $qs = parse_url($chemin, PHP_URL_QUERY);
        if ($qs) { parse_str($qs, $get); }
        return $this->app->executer(new Request($m, parse_url($chemin, PHP_URL_PATH) ?: '/', $get, $post, '127.0.0.1'));
    }

    public function testAccesRefuseSansAdmin(): void
    {
        self::assertSame(302, $this->req('GET', '/admin')->statut);
        $m = $this->app->service(SerieRepository::class)->parCode('M')['id'];
        $cap = $this->app->service(UtilisateurRepository::class)->creer(['identifiant' => 'SALON', 'serie_id' => $m, 'hash_sha1' => sha1('x')]);
        $this->app->session()->demarrer(); $this->app->session()->set('utilisateur_id', $cap);
        self::assertSame(403, $this->req('GET', '/admin')->statut);
    }

    public function testPagesAdminEtActions(): void
    {
        $this->app->session()->demarrer(); $this->app->session()->set('utilisateur_id', $this->adminId);
        self::assertSame(200, $this->req('GET', '/admin')->statut);
        self::assertSame(200, $this->req('GET', '/admin/alertes')->statut);
        self::assertStringContainsString('ADMIN', $this->req('GET', '/admin/utilisateurs')->corps);

        $m = $this->app->service(SerieRepository::class)->parCode('M')['id'];
        self::assertSame(302, $this->req('POST', '/admin/utilisateurs', ['identifiant' => 'GAP', 'serie_id' => $m, 'equipe_id' => ''])->statut);
        $gap = $this->app->service(UtilisateurRepository::class)->parIdentifiantEtSerie('GAP', $m);
        self::assertNotNull($gap);
        self::assertSame(302, $this->req('POST', '/admin/utilisateurs/' . $gap['id'] . '/reinitialiser')->statut);
        self::assertStringStartsWith('$2y$', $this->app->service(UtilisateurRepository::class)->parId((int) $gap['id'])['hash_bcrypt']);
        $this->req('POST', '/admin/utilisateurs/' . $gap['id'] . '/admin', ['valeur' => '1']);
        self::assertSame(1, (int) $this->app->service(UtilisateurRepository::class)->parId((int) $gap['id'])['est_admin']);

        $g = $this->app->service(GolfRepository::class)->trouverOuCreer('GAP');
        $j = $this->app->service(JoueurRepository::class);
        $a = $j->trouverOuCreer($g, 'DUPONT', 'H', 12.0, null); $b = $j->trouverOuCreer($g, 'DUPOND', 'H', 12.0, null);
        $pageJoueurs = $this->req('GET', '/admin/joueurs?golf=' . $g)->corps;
        self::assertStringContainsString('DUPOND', $pageJoueurs);
        self::assertStringContainsString('form="joueur-' . $a . '"', $pageJoueurs);
        self::assertStringContainsString('id="joueur-' . $a . '"', $pageJoueurs);
        self::assertStringNotContainsString('<tr><form', $pageJoueurs);
        self::assertSame(302, $this->req('POST', '/admin/joueurs/fusion', ['source_id' => $b, 'cible_id' => $a])->statut);
        self::assertSame((string) $a, (string) $j->parId($b)['fusionne_dans']);
        $this->req('POST', '/admin/joueurs/' . $a, ['nom' => 'Dupont', 'prenom' => 'Paul', 'sexe' => 'H']);
        self::assertSame(['DUPONT', 'Paul'], [$j->parId($a)['nom'], $j->parId($a)['prenom']]);
    }

    public function testCsrfObligatoire(): void
    {
        $this->app->session()->demarrer(); $this->app->session()->set('utilisateur_id', $this->adminId);
        $rep = $this->app->executer(new Request('POST', '/admin/utilisateurs', [], ['identifiant' => 'X'], ''));
        self::assertSame(400, $rep->statut);
    }
}
