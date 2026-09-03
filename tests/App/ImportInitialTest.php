<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Migrations;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;
use Agca\App\Service\ImportInitial;

final class ImportInitialTest extends DbTestCase
{
    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new App($this->config());
        $sql = file_get_contents(dirname(__DIR__) . '/fixtures/ancien_mini.sql');
        foreach (array_filter(array_map('trim', explode(";\n", $sql))) as $stmt) { $this->db->pdo()->exec($stmt); }
    }

    private function donnees(): array
    {
        $d = require dirname(__DIR__, 2) . '/db/import/saison_2026_27.php';
        // Réduit aux équipes du jeu de test.
        $d['divisions'] = ['M' => [['DIV2/POULE B', [1 => 'SAINT-MARTIN-2', 2 => 'LUBERON-1', 3 => 'SALON', 4 => 'FREGATE']]], 'H1' => [['DIVISION 1', [1 => 'VALGARDE-1', 2 => 'SALON', 3 => 'ORANGE', 4 => 'VICTORIA']]]];
        return $d;
    }

    public function testImportComplet(): void
    {
        $r = $this->app->service(ImportInitial::class)->executer($this->donnees(), ['id' => null, 'identifiant' => 'import', 'est_admin' => 1]);
        self::assertSame([], $r['erreurs'], implode("\n", $r['erreurs']));
        $saison = $this->app->service(SaisonRepository::class)->active();
        self::assertSame('2026-27', $saison['libelle']);
        $series = $this->app->service(SerieRepository::class);
        $m = (int) $series->parCode('M')['id']; $h1 = (int) $series->parCode('H1')['id'];
        $equipes = $this->app->service(EquipeRepository::class);
        // VALGARDE-2 exclue, SAINTE-MAXIME importée mais sans division, SALON existe dans les deux séries avec deux capitaines.
        self::assertNull($equipes->parNomEtSerie('VALGARDE-2', $m));
        self::assertSame('CHARPENTIER', $equipes->parNomEtSerie('SAINTE-MAXIME', $m)['capitaine_nom']);
        self::assertSame('TRAPY', $equipes->parNomEtSerie('SALON', $m)['capitaine_nom']);
        self::assertSame('Thibault', $equipes->parNomEtSerie('SALON', $h1)['capitaine_nom']);
        self::assertSame('SAINT-MARTIN', $equipes->parNomEtSerie('SAINT-MARTIN-2', $m)['golf_nom']);
        self::assertSame((int) $equipes->parNomEtSerie('SALON', $m)['golf_id'], (int) $equipes->parNomEtSerie('SALON', $h1)['golf_id']);
        self::assertNull($equipes->parNomEtSerie('LUBERON', $h1));
        // identifiants : dernier ID gagne, série distinguée, ADMIN unique
        $u = $this->app->service(UtilisateurRepository::class);
        self::assertSame('3527d9c343f46bfbe1147bb7d19e869435095958', $u->parIdentifiantEtSerie('SALON', $m)['hash_sha1']);
        self::assertSame('4f6c12e311de54b58f83bfd4228730b2b53fe37c', $u->parIdentifiantEtSerie('SALON', $h1)['hash_sha1']);
        self::assertSame((int) $equipes->parNomEtSerie('SALON', $h1)['id'], (int) $u->parIdentifiantEtSerie('SALON', $h1)['equipe_id']);
        self::assertSame(1, (int) $u->parIdentifiantEtSerie('ADMIN', null)['est_admin']);
        self::assertCount(1, array_filter($u->tous(), fn($x) => $x['identifiant'] === 'ADMIN'));
        self::assertNull($u->parIdentifiantEtSerie('VALGARDE-2', $m));
        // divisions et rencontres
        $div = $this->app->service(DivisionRepository::class)->parSaisonEtSerie((int) $saison['id'], $m);
        self::assertSame(['DIV2/POULE B'], array_column($div, 'libelle'));
        $rencontres = $this->app->service(RencontreRepository::class)->parDivision((int) $div[0]['id']);
        self::assertCount(12, $rencontres);
        self::assertSame(['SAINT-MARTIN-2', 'LUBERON-1', '2026-09-26'], [$rencontres[0]['recevant_nom'], $rencontres[0]['invite_nom'], $rencontres[0]['date_reelle']]);
        self::assertCount(12, $this->app->service(RencontreRepository::class)->parDivision((int) $this->app->service(DivisionRepository::class)->parSaisonEtSerie((int) $saison['id'], $h1)[0]['id']));
        self::assertSame(0, (int) $this->db->one('SELECT COUNT(*) AS n FROM agca_joueur')['n']);
    }

    public function testRefuseSiSaisonExiste(): void
    {
        $svc = $this->app->service(ImportInitial::class);
        $svc->executer($this->donnees(), ['id' => null, 'est_admin' => 1]);
        $r = $svc->executer($this->donnees(), ['id' => null, 'est_admin' => 1]);
        self::assertNotSame([], $r['erreurs']);
    }

    public function testEchecEnCoursDImportNeLaisseRien(): void
    {
        $d = $this->donnees();
        $d['divisions']['M'][0][1][4] = 'INCONNUE';
        $r = $this->app->service(ImportInitial::class)->executer($d, ['id' => null, 'est_admin' => 1]);
        self::assertNotSame([], $r['erreurs']);
        self::assertSame([], $this->app->service(SaisonRepository::class)->toutes());
        self::assertSame(0, (int) $this->db->one('SELECT COUNT(*) AS n FROM agca_equipe')['n']);
        self::assertSame(0, (int) $this->db->one('SELECT COUNT(*) AS n FROM agca_utilisateur')['n']);
    }
}
