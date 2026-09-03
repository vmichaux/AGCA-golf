<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JoueurRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\PartieRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;
use Agca\App\Service\EnregistrementFeuille;
use Agca\App\Service\Mailer;
use Agca\Domain\Serie;

final class EnregistrementFeuilleTest extends DbTestCase
{
    private App $app; private int $rencontre; private array $capitaine; private int $golfRec; private int $golfInv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new App($this->config());
        $saison = $this->app->service(SaisonRepository::class)->creer('2026-27', '2026-09-01', '2027-06-30');
        $h1 = $this->app->service(SerieRepository::class)->parCode('H1')['id'];
        $golfs = $this->app->service(GolfRepository::class); $equipes = $this->app->service(EquipeRepository::class);
        $this->golfRec = $golfs->trouverOuCreer('VALGARDE'); $this->golfInv = $golfs->trouverOuCreer('SALON');
        $rec = $equipes->creer(['golf_id' => $this->golfRec, 'serie_id' => $h1, 'nom' => 'VALGARDE-1', 'capitaine_email' => 'valgarde@test']);
        $inv = $equipes->creer(['golf_id' => $this->golfInv, 'serie_id' => $h1, 'nom' => 'SALON', 'capitaine_email' => 'salon@test']);
        $u = $this->app->service(UtilisateurRepository::class);
        $this->capitaine = $u->parId($u->creer(['identifiant' => 'VALGARDE-1', 'serie_id' => $h1, 'equipe_id' => $rec, 'hash_sha1' => sha1('x')]));
        $d = $this->app->service(DivisionRepository::class)->creer($saison, $h1, 'DIVISION 1', 1);
        $j = $this->app->service(JourneeRepository::class)->creer($saison, $h1, 1, 'aller', '2026-10-24');
        $this->rencontre = $this->app->service(RencontreRepository::class)->creer($d, $j, $rec, $inv, '2026-10-24');
    }

    private function post(): array
    {
        $p = [];
        $p[1] = ['rec1_nom' => 'Thomas', 'rec1_index' => '4,2', 'rec2_nom' => 'Lecubin', 'rec2_index' => '6.0', 'inv1_nom' => 'Thibault', 'inv1_index' => '3.1', 'inv2_nom' => 'Martin', 'inv2_index' => '5', 'resultat' => 'G', 'score' => '3&2'];
        foreach ([2, 3, 4, 5] as $n) {
            $p[$n] = ['rec1_nom' => "Rec$n", 'rec1_index' => '8', 'inv1_nom' => "Inv$n", 'inv1_index' => '9', 'resultat' => $n === 5 ? 'P' : ($n === 4 ? 'N' : 'G'), 'score' => $n === 4 ? 'AS' : '1UP'];
        }
        return ['date_reelle' => '2026-10-24', 'p' => $p];
    }

    public function testStructureH1(): void
    {
        $s = $this->app->service(EnregistrementFeuille::class)->structure(Serie::h1());
        self::assertSame([['numero' => 1, 'type' => 'double'], ['numero' => 2, 'type' => 'simple'], ['numero' => 3, 'type' => 'simple'], ['numero' => 4, 'type' => 'simple'], ['numero' => 5, 'type' => 'simple']], $s);
        self::assertCount(15, $this->app->service(EnregistrementFeuille::class)->structure(Serie::mixte()));
    }

    public function testEnregistrementComplet(): void
    {
        $svc = $this->app->service(EnregistrementFeuille::class);
        $r = $svc->enregistrer($this->rencontre, $this->post(), $this->capitaine);
        self::assertSame(['erreurs' => [], 'alertes' => []], $r);
        $ligne = $this->app->service(RencontreRepository::class)->parId($this->rencontre);
        // G G G N P : 3+3+3+2+1 = 12 pour, 1+1+1+2+3 = 8 contre
        self::assertSame(['enregistree', 12, 8, '3.0', '1.0', '0.0'], [$ligne['statut'], (int) $ligne['total_pour'], (int) $ligne['total_contre'], $ligne['pts_rencontre_pour'], $ligne['pts_rencontre_contre'], $ligne['bonus_invite']]);
        $parties = $this->app->service(PartieRepository::class)->parRencontre($this->rencontre);
        self::assertSame(['THOMAS', 'LECUBIN', 'THIBAULT', 'MARTIN'], [$parties[0]['rec_nom1'], $parties[0]['rec_nom2'], $parties[0]['inv_nom1'], $parties[0]['inv_nom2']]);
        self::assertSame(['4.2', 3, 2, 3, 1], [$parties[0]['rec_index1'], (int) $parties[0]['score_trous'], (int) $parties[0]['score_restants'], (int) $parties[0]['pts_pour'], (int) $parties[0]['pts_contre']]);
        self::assertSame(1, (int) $parties[3]['score_as']);
        self::assertSame($this->golfRec, (int) $this->app->service(JoueurRepository::class)->parId((int) $parties[0]['rec_joueur1_id'])['golf_id']);
        self::assertSame($this->golfInv, (int) $this->app->service(JoueurRepository::class)->parId((int) $parties[0]['inv_joueur1_id'])['golf_id']);
        $mail = $this->app->service(Mailer::class)->dernierEnvoi();
        self::assertSame('salon@test', $mail['a']);
        self::assertStringContainsString('12 - 8', $mail['texte']);
    }

    public function testFeuilleIncompleteEnregistreeAvecAlerte(): void
    {
        $post = $this->post();
        $post['p'][3]['inv1_nom'] = '';
        $post['p'][5]['resultat'] = '';
        $r = $this->app->service(EnregistrementFeuille::class)->enregistrer($this->rencontre, $post, $this->capitaine);
        self::assertSame([], $r['erreurs']);
        self::assertSame(['FEUILLE_INCOMPLETE'], $r['alertes']);
        self::assertSame('enregistree', $this->app->service(RencontreRepository::class)->parId($this->rencontre)['statut']);
    }

    public function testErreursBloquantes(): void
    {
        $post = $this->post();
        $post['p'][2]['rec1_index'] = 'abc';
        $post['p'][3]['score'] = '40et';
        $post['date_reelle'] = '31/02/2026';
        $r = $this->app->service(EnregistrementFeuille::class)->enregistrer($this->rencontre, $post, $this->capitaine);
        self::assertCount(3, $r['erreurs']);
        self::assertSame('a_jouer', $this->app->service(RencontreRepository::class)->parId($this->rencontre)['statut']);
    }

    public function testInviteNePeutPasSaisir(): void
    {
        $u = $this->app->service(UtilisateurRepository::class);
        $inv = $u->parId($u->creer(['identifiant' => 'SALON', 'serie_id' => $this->capitaine['serie_id'], 'equipe_id' => $this->app->service(EquipeRepository::class)->parNomEtSerie('SALON', (int) $this->capitaine['serie_id'])['id'], 'hash_sha1' => sha1('x')]));
        $this->expectException(\Agca\App\Http\HttpException::class);
        $this->app->service(EnregistrementFeuille::class)->enregistrer($this->rencontre, $this->post(), $inv);
    }

    public function testDeuxiemeSaisieRefuseeSaufAdmin(): void
    {
        $svc = $this->app->service(EnregistrementFeuille::class);
        $svc->enregistrer($this->rencontre, $this->post(), $this->capitaine);
        try { $svc->enregistrer($this->rencontre, $this->post(), $this->capitaine); self::fail(); } catch (\Agca\App\Http\HttpException $e) { self::assertSame(403, $e->statut); }
        $admin = ['id' => 99, 'identifiant' => 'ADMIN', 'est_admin' => 1, 'equipe_id' => null];
        $post = $this->post(); $post['p'][5]['resultat'] = 'G';
        self::assertSame([], $svc->enregistrer($this->rencontre, $post, $admin)['erreurs']);
        self::assertSame(14, (int) $this->app->service(RencontreRepository::class)->parId($this->rencontre)['total_pour']);
        self::assertStringContainsString('corrigée', $this->app->service(Mailer::class)->dernierEnvoi()['sujet']);
    }

    public function testChangerDate(): void
    {
        $svc = $this->app->service(EnregistrementFeuille::class);
        self::assertNull($svc->changerDate($this->rencontre, '2026-11-07', $this->capitaine));
        $l = $this->app->service(RencontreRepository::class)->parId($this->rencontre);
        self::assertSame(['2026-11-07', 1], [$l['date_reelle'], (int) $l['reportee']]);
        self::assertStringContainsString('Report', $this->app->service(Mailer::class)->dernierEnvoi()['sujet']);
        self::assertNotNull($svc->changerDate($this->rencontre, 'n importe quoi', $this->capitaine));
        self::assertNull($svc->changerDate($this->rencontre, '2026-10-24', $this->capitaine));
        self::assertSame(0, (int) $this->app->service(RencontreRepository::class)->parId($this->rencontre)['reportee']);
    }
}
