<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Http\HttpException;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;
use Agca\App\Service\AccesRencontre;

final class AccesRencontreTest extends DbTestCase
{
    private App $app; private int $rencontre; private array $recevant; private array $invite; private array $tiers; private array $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new App($this->config());
        $saison = $this->app->service(SaisonRepository::class)->creer('2026-27', '2026-09-01', '2027-06-30');
        $h1 = $this->app->service(SerieRepository::class)->parCode('H1')['id'];
        $golfs = $this->app->service(GolfRepository::class); $equipes = $this->app->service(EquipeRepository::class); $u = $this->app->service(UtilisateurRepository::class);
        $ids = [];
        foreach (['VALGARDE-1', 'SALON', 'ORANGE'] as $nom) {
            $e = $equipes->creer(['golf_id' => $golfs->trouverOuCreer($nom), 'serie_id' => $h1, 'nom' => $nom]);
            $ids[$nom] = $u->parId($u->creer(['identifiant' => $nom, 'serie_id' => $h1, 'equipe_id' => $e, 'hash_sha1' => sha1('x')]));
        }
        $this->admin = $u->parId($u->creer(['identifiant' => 'ADMIN', 'serie_id' => null, 'hash_sha1' => sha1('x'), 'est_admin' => 1]));
        $d = $this->app->service(DivisionRepository::class)->creer($saison, $h1, 'DIVISION 1', 1);
        $j = $this->app->service(JourneeRepository::class)->creer($saison, $h1, 1, 'aller', '2026-10-24');
        $this->rencontre = $this->app->service(RencontreRepository::class)->creer($d, $j, (int) $ids['VALGARDE-1']['equipe_id'], (int) $ids['SALON']['equipe_id'], '2026-10-24');
        [$this->recevant, $this->invite, $this->tiers] = [$ids['VALGARDE-1'], $ids['SALON'], $ids['ORANGE']];
    }

    public function testCapitainesConcernesEtAdminAccedent(): void
    {
        $acces = $this->app->service(AccesRencontre::class);
        self::assertSame('VALGARDE-1', $acces->charger($this->rencontre, $this->recevant)['recevant_nom']);
        self::assertSame('SALON', $acces->charger($this->rencontre, $this->invite)['invite_nom']);
        self::assertNotNull($acces->charger($this->rencontre, $this->admin));
    }

    public function testTiersEtAnonymeRefuses(): void
    {
        $acces = $this->app->service(AccesRencontre::class);
        try { $acces->charger($this->rencontre, $this->tiers); self::fail(); } catch (HttpException $e) { self::assertSame(403, $e->statut); }
        try { $acces->charger($this->rencontre, null); self::fail(); } catch (HttpException $e) { self::assertSame(403, $e->statut); }
        try { $acces->charger(9999, $this->admin); self::fail(); } catch (HttpException $e) { self::assertSame(404, $e->statut); }
    }

    public function testDroitsDeSaisie(): void
    {
        $acces = $this->app->service(AccesRencontre::class);
        $r = $acces->charger($this->rencontre, $this->admin);
        self::assertTrue($acces->peutSaisir($r, $this->recevant));
        self::assertFalse($acces->peutSaisir($r, $this->invite));
        self::assertTrue($acces->peutSaisir($r, $this->admin));
        $this->app->service(RencontreRepository::class)->mettreAJourResultat($this->rencontre, ['statut' => 'enregistree']);
        $r = $acces->charger($this->rencontre, $this->admin);
        self::assertFalse($acces->peutSaisir($r, $this->recevant));
        self::assertTrue($acces->peutSaisir($r, $this->admin));
        self::assertFalse($acces->peutModifierDate($r, $this->recevant));
        self::assertTrue($acces->peutModifierDate($r, $this->admin));
        $this->app->service(SaisonRepository::class)->changerStatut((int) $r['saison_id'], 'gelee');
        $r = $acces->charger($this->rencontre, $this->admin);
        self::assertFalse($acces->peutSaisir($r, $this->admin));
    }
}
