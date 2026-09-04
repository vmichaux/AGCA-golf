<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Repository\ActualiteRepository;
use Agca\App\Repository\AlbumRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\DocumentRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\OrganigrammeRepository;
use Agca\App\Repository\PageRepository;

final class ContenuRepositoriesTest extends DbTestCase
{
    private App $app;
    protected function setUp(): void { parent::setUp(); $this->app = new App($this->config()); }

    public function testPagesEtDocuments(): void
    {
        $pages = $this->app->service(PageRepository::class); $docs = $this->app->service(DocumentRepository::class);
        $id = $pages->creer(['slug' => 'statuts', 'titre' => 'Statuts', 'corps_md' => '# S', 'corps_html' => '<h2>S</h2>', 'systeme' => 1, 'dans_menu' => 1, 'ordre' => 2, 'modifie_par' => null]);
        $libre = $pages->creer(['slug' => 'libre', 'titre' => 'Libre', 'corps_md' => '', 'corps_html' => '', 'systeme' => 0, 'dans_menu' => 0, 'ordre' => 1, 'modifie_par' => null]);
        self::assertSame('Statuts', $pages->parSlug('statuts')['titre']);
        self::assertSame(['libre', 'statuts'], array_column($pages->toutes(), 'slug'));
        self::assertSame(['statuts'], array_column($pages->menu(), 'slug'));
        $pages->modifier($id, ['titre' => 'Statuts 2026', 'corps_md' => 'x', 'corps_html' => '<p>x</p>']);
        self::assertSame('Statuts 2026', $pages->parId($id)['titre']);
        self::assertFalse($pages->supprimer($id));
        self::assertTrue($pages->supprimer($libre));
        self::assertNull($pages->parId($libre));
        $d1 = $docs->creer(['titre' => 'Statuts PDF', 'fichier' => '2026-09-04-statuts-abc123.pdf', 'type_mime' => 'application/pdf', 'taille' => 1234, 'categorie' => 'statuts', 'televerse_par' => null]);
        $d2 = $docs->creer(['titre' => 'AG 2025', 'fichier' => '2026-09-04-ag-def456.pdf', 'type_mime' => 'application/pdf', 'taille' => 99, 'categorie' => 'ag', 'televerse_par' => null]);
        $pages->definirDocuments($id, [$d2, $d1]);
        self::assertSame(['AG 2025', 'Statuts PDF'], array_column($pages->documents($id), 'titre'));
        self::assertTrue($docs->estUtilise($d1));
        self::assertSame(['Statuts PDF'], array_column($docs->parCategorie('statuts'), 'titre'));
        $pages->definirDocuments($id, []);
        self::assertFalse($docs->estUtilise($d1));
        $docs->supprimer($d1);
        self::assertNull($docs->parId($d1));
    }

    public function testActualites(): void
    {
        $r = $this->app->service(ActualiteRepository::class);
        $a = $r->creer(['titre' => 'Ancienne', 'slug' => 'ancienne', 'date_publication' => '2026-01-10', 'resume' => 'r', 'corps_md' => 'a', 'corps_html' => '<p>a</p>', 'publie' => 1, 'modifie_par' => null]);
        $b = $r->creer(['titre' => 'Récente', 'slug' => 'recente', 'date_publication' => '2026-09-01', 'resume' => null, 'corps_md' => 'b', 'corps_html' => '<p>b</p>', 'publie' => 1, 'modifie_par' => null]);
        $c = $r->creer(['titre' => 'Brouillon', 'slug' => 'brouillon', 'date_publication' => '2026-09-02', 'resume' => null, 'corps_md' => 'c', 'corps_html' => '<p>c</p>', 'publie' => 0, 'modifie_par' => null]);
        self::assertSame(['Récente', 'Ancienne'], array_column($r->publiees(), 'titre'));
        self::assertSame(['Ancienne'], array_column($r->publiees(1, 1), 'titre'));
        self::assertSame(2, $r->compterPubliees());
        self::assertSame(['Brouillon', 'Récente', 'Ancienne'], array_column($r->toutes(), 'titre'));
        $r->modifier($c, ['publie' => 1]);
        self::assertSame(3, $r->compterPubliees());
        $r->supprimer($a);
        self::assertNull($r->parId($a));
    }

    public function testCompetitionsEtPalmares(): void
    {
        $r = $this->app->service(CompetitionRepository::class); $docs = $this->app->service(DocumentRepository::class);
        $m = $r->creer(['code' => 'master', 'nom' => 'Le Master', 'accroche' => 'Clôture', 'formule' => 'Stroke play', 'corps_md' => '', 'corps_html' => '', 'ordre' => 4, 'actif' => 1]);
        $t = $r->creer(['code' => 'tournoi', 'nom' => 'Le Tournoi', 'accroche' => null, 'formule' => null, 'corps_md' => '', 'corps_html' => '', 'ordre' => 2, 'actif' => 0]);
        self::assertSame(['master'], array_column($r->toutes(), 'code'));
        self::assertSame(['tournoi', 'master'], array_column($r->toutes(false), 'code'));
        $d = $docs->creer(['titre' => 'Résultats', 'fichier' => '2026-09-04-res-xyz789.pdf', 'type_mime' => 'application/pdf', 'taille' => 1, 'categorie' => 'palmares', 'televerse_par' => null]);
        $p1 = $r->ajouterPalmares(['competition_id' => $m, 'saison' => '2024', 'lieu' => 'Valcros', 'vainqueur' => 'Orange', 'detail_md' => null, 'detail_html' => null, 'document_id' => null, 'ordre' => 0]);
        $p2 = $r->ajouterPalmares(['competition_id' => $m, 'saison' => '2025', 'lieu' => 'Gap', 'vainqueur' => 'Valgarde', 'detail_md' => 'ok', 'detail_html' => '<p>ok</p>', 'document_id' => $d, 'ordre' => 0]);
        self::assertSame(['2025', '2024'], array_column($r->palmares($m), 'saison'));
        self::assertSame('Résultats', $r->palmares($m)[0]['document_titre']);
        self::assertSame('Valgarde', $r->dernierPalmares($m)['vainqueur']);
        self::assertTrue($docs->estUtilise($d));
        $r->modifierPalmares($p1, ['vainqueur' => 'Orange 2']);
        self::assertSame('Orange 2', $r->palmaresParId($p1)['vainqueur']);
        $r->supprimerPalmares($p2);
        self::assertCount(1, $r->palmares($m));
        $r->modifier($t, ['actif' => 1, 'nom' => 'Le Tournoi de l\'Amitié']);
        self::assertSame('Le Tournoi de l\'Amitié', $r->parCode('tournoi')['nom']);
    }

    public function testGolfsOrganigrammeAlbums(): void
    {
        $golfs = $this->app->service(GolfRepository::class);
        $a = $golfs->trouverOuCreer('VALGARDE'); $b = $golfs->trouverOuCreer('AIX-EN-PROVENCE'); $c = $golfs->trouverOuCreer('ANCIEN');
        $golfs->modifier($a, ['ville' => 'La Crau', 'site_web' => 'www.golf-valgarde.com', 'ordre' => 0, 'membre' => 1]);
        $golfs->modifier($c, ['membre' => 0]);
        self::assertSame(['AIX-EN-PROVENCE', 'VALGARDE'], array_column($golfs->membres(), 'nom'));
        self::assertSame('La Crau', $golfs->parId($a)['ville']);
        $org = $this->app->service(OrganigrammeRepository::class);
        $org->creer(['groupe' => 'ca', 'fonction' => 'Membre', 'prenom' => 'A', 'nom' => 'B', 'golf' => 'Salon', 'ordre' => 1]);
        $p = $org->creer(['groupe' => 'bureau', 'fonction' => 'Président', 'prenom' => 'Guy', 'nom' => 'POMET', 'golf' => 'Valgarde', 'ordre' => 0]);
        $g = $org->parGroupe();
        self::assertSame(['POMET'], array_column($g['bureau'], 'nom'));
        self::assertSame(['B'], array_column($g['ca'], 'nom'));
        $org->modifier($p, ['fonction' => 'Président d\'honneur']);
        self::assertSame('Président d\'honneur', $org->parId($p)['fonction']);
        $org->supprimer($p);
        self::assertSame([], $org->parGroupe()['bureau']);
        $albums = $this->app->service(AlbumRepository::class);
        $albums->creer(['titre' => 'Trophée 2024', 'annee' => 2024, 'url' => '/imagesTROPHEE_2024/', 'ordre' => 0]);
        $x = $albums->creer(['titre' => 'Trophée 2025', 'annee' => 2025, 'url' => '/imagesTROPHEE_2025/', 'ordre' => 0]);
        self::assertSame(['Trophée 2025', 'Trophée 2024'], array_column($albums->tous(), 'titre'));
        $albums->modifier($x, ['titre' => 'Trophée 2025 à Valgarde']);
        self::assertSame('Trophée 2025 à Valgarde', $albums->parId($x)['titre']);
        $albums->supprimer($x);
        self::assertCount(1, $albums->tous());
    }
}
