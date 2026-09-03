<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Auth;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;

final class AuthTest extends DbTestCase
{
    private App $app;
    private int $idSalonM;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $this->app = new App($this->config());
        $series = $this->app->service(SerieRepository::class);
        $u = $this->app->service(UtilisateurRepository::class);
        $this->idSalonM = $u->creer(['identifiant' => 'SALON', 'serie_id' => $series->parCode('M')['id'], 'hash_sha1' => sha1('golf')]);
        $u->creer(['identifiant' => 'SALON', 'serie_id' => $series->parCode('H1')['id'], 'hash_sha1' => sha1('autre')]);
        $u->creer(['identifiant' => 'ADMIN', 'serie_id' => null, 'hash_sha1' => sha1('secret'), 'est_admin' => 1]);
    }

    public function testConnexionMigreLeSha1EnBcrypt(): void
    {
        $auth = $this->app->auth();
        self::assertSame(['ok' => true, 'erreur' => null], $auth->connecter('salon', 'M', 'golf'));
        self::assertTrue($auth->estConnecte());
        self::assertSame($this->idSalonM, (int) $auth->utilisateur()['id']);
        $l = $this->app->service(UtilisateurRepository::class)->parId($this->idSalonM);
        self::assertNull($l['hash_sha1']);
        self::assertStringStartsWith('$2y$', $l['hash_bcrypt']);
        $auth->deconnecter();
        self::assertFalse($auth->estConnecte());
        self::assertTrue($auth->connecter('SALON', 'M', 'golf')['ok']);
    }

    public function testLaSerieDistingueLesHomonymes(): void
    {
        self::assertFalse($this->app->auth()->connecter('SALON', 'H1', 'golf')['ok']);
        self::assertTrue($this->app->auth()->connecter('SALON', 'H1', 'autre')['ok']);
    }

    public function testAdminSeConnecteQuelleQueSoitLaSerie(): void
    {
        $auth = $this->app->auth();
        self::assertTrue($auth->connecter('ADMIN', 'M', 'secret')['ok']);
        self::assertTrue($auth->estAdmin());
    }

    public function testBlocageApresCinqEchecs(): void
    {
        $auth = $this->app->auth();
        for ($i = 0; $i < 5; $i++) { self::assertFalse($auth->connecter('SALON', 'M', 'faux')['ok']); }
        $r = $auth->connecter('SALON', 'M', 'golf');
        self::assertFalse($r['ok']);
        self::assertStringContainsString('bloqué', $r['erreur']);
    }

    public function testChangementDeMotDePasse(): void
    {
        $auth = $this->app->auth();
        $auth->connecter('SALON', 'M', 'golf');
        self::assertNotNull($auth->changerMotDePasse($this->idSalonM, 'faux', 'nouveau-mdp'));
        self::assertNotNull($auth->changerMotDePasse($this->idSalonM, 'golf', 'court'));
        self::assertNull($auth->changerMotDePasse($this->idSalonM, 'golf', 'nouveau-mdp'));
        $auth->deconnecter();
        self::assertTrue($auth->connecter('SALON', 'M', 'nouveau-mdp')['ok']);
    }
}
