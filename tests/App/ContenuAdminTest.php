<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Http\Request;
use Agca\App\Repository\ActualiteRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\DocumentRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\PageRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;

final class ContenuAdminTest extends DbTestCase
{
    private App $app;

    protected function setUp(): void
    {
        parent::setUp(); $_SESSION = [];
        $this->app = new App($this->config());
        $u = $this->app->service(UtilisateurRepository::class);
        $admin = $u->creer(['identifiant' => 'ADMIN', 'serie_id' => null, 'hash_sha1' => sha1('x'), 'est_admin' => 1]);
        $this->app->session()->demarrer(); $this->app->session()->set('utilisateur_id', $admin);
    }

    private function req(string $m, string $chemin, array $post = [], array $fichiers = []): \Agca\App\Http\Response
    {
        if ($m === 'POST') { $post['_csrf'] = $this->app->session()->csrf(); }
        $get = []; $qs = parse_url($chemin, PHP_URL_QUERY); if ($qs) { parse_str($qs, $get); }
        return $this->app->executer(new Request($m, (string) parse_url($chemin, PHP_URL_PATH), $get, $post, '127.0.0.1', $fichiers));
    }

    public function testTableauDeBordEtPages(): void
    {
        self::assertSame(200, $this->req('GET', '/admin/contenu')->statut);
        self::assertSame(302, $this->req('POST', '/admin/contenu/pages', ['slug' => 'voyage', 'titre' => 'Voyage', 'corps_md' => "# Voyage\n\nTexte **fort**.", 'dans_menu' => '1', 'ordre' => '3'])->statut);
        $p = $this->app->service(PageRepository::class)->parSlug('voyage');
        self::assertSame("<h2>Voyage</h2>\n<p>Texte <strong>fort</strong>.</p>", $p['corps_html']);
        self::assertStringContainsString('Voyage', $this->req('GET', '/admin/contenu/pages')->corps);
        self::assertStringContainsString('Texte **fort**', $this->req('GET', '/admin/contenu/pages/' . $p['id'])->corps);
        // slug invalide et doublon refusés
        self::assertSame(422, $this->req('POST', '/admin/contenu/pages', ['slug' => 'Mauvais Slug', 'titre' => 'x', 'corps_md' => ''])->statut);
        self::assertSame(422, $this->req('POST', '/admin/contenu/pages', ['slug' => 'voyage', 'titre' => 'x', 'corps_md' => ''])->statut);
        $this->req('POST', '/admin/contenu/pages/' . $p['id'], ['slug' => 'voyage-2026', 'titre' => 'Voyage 2026', 'corps_md' => 'nouveau', 'ordre' => '1']);
        self::assertSame('Voyage 2026', $this->app->service(PageRepository::class)->parSlug('voyage-2026')['titre']);
        self::assertSame(302, $this->req('POST', '/admin/contenu/pages/' . $p['id'] . '/supprimer')->statut);
        self::assertNull($this->app->service(PageRepository::class)->parId((int) $p['id']));
    }

    public function testActualites(): void
    {
        self::assertSame(302, $this->req('POST', '/admin/contenu/actualites', ['titre' => 'La saison démarre !', 'date_publication' => '2026-09-01', 'resume' => 'r', 'corps_md' => 'corps', 'publie' => '1'])->statut);
        $a = $this->app->service(ActualiteRepository::class)->toutes()[0];
        self::assertSame('la-saison-demarre', $a['slug']);
        self::assertSame(1, (int) $a['publie']);
        $this->req('POST', '/admin/contenu/actualites/' . $a['id'], ['titre' => $a['titre'], 'date_publication' => '2026-09-01', 'resume' => '', 'corps_md' => 'corps 2']);
        $a2 = $this->app->service(ActualiteRepository::class)->parId((int) $a['id']);
        self::assertSame([0, '<p>corps 2</p>'], [(int) $a2['publie'], $a2['corps_html']]);
        self::assertSame(422, $this->req('POST', '/admin/contenu/actualites', ['titre' => '', 'date_publication' => 'x', 'corps_md' => ''])->statut);
        self::assertSame(302, $this->req('POST', '/admin/contenu/actualites/' . $a['id'] . '/supprimer')->statut);
        self::assertCount(0, $this->app->service(ActualiteRepository::class)->toutes());
    }

    public function testCompetitionsPalmaresGolfsOrganigrammeAlbums(): void
    {
        $c = $this->app->service(CompetitionRepository::class);
        $id = $c->creer(['code' => 'master', 'nom' => 'Le Master', 'accroche' => null, 'formule' => null, 'corps_md' => '', 'corps_html' => '', 'ordre' => 1, 'actif' => 1]);
        self::assertStringContainsString('Le Master', $this->req('GET', '/admin/contenu/competitions')->corps);
        $this->req('POST', "/admin/contenu/competitions/$id", ['nom' => 'Le Master', 'accroche' => 'Clôture', 'formule' => 'Stroke play', 'corps_md' => 'Réservé', 'ordre' => '4', 'actif' => '1']);
        self::assertSame('<p>Réservé</p>', $c->parId($id)['corps_html']);
        $this->req('POST', "/admin/contenu/competitions/$id/palmares", ['saison' => '2025', 'lieu' => 'Gap', 'vainqueur' => 'Valgarde', 'detail_md' => '- 1er Valgarde']);
        $pal = $c->palmares($id)[0];
        self::assertSame("<ul>\n<li>1er Valgarde</li>\n</ul>", $pal['detail_html']);
        $this->req('POST', '/admin/contenu/palmares/' . $pal['id'], ['saison' => '2025', 'lieu' => 'Gap', 'vainqueur' => 'Valgarde et Orange', 'detail_md' => '']);
        self::assertSame('Valgarde et Orange', $c->palmaresParId((int) $pal['id'])['vainqueur']);
        // document_id inexistant refusé (stocké null)
        $this->req('POST', '/admin/contenu/palmares/' . $pal['id'], ['saison' => '2025', 'lieu' => 'Gap', 'vainqueur' => 'Valgarde et Orange', 'detail_md' => '', 'document_id' => '9999']);
        self::assertNull($c->palmaresParId((int) $pal['id'])['document_id']);
        // document_id existant accepté
        $docId = $this->app->service(DocumentRepository::class)->creer(['titre' => 'Palmarès PDF', 'fichier' => '2026-09-04-test-aaaaaa.pdf', 'type_mime' => 'application/pdf', 'taille' => 10, 'categorie' => 'palmares', 'televerse_par' => null]);
        $this->req('POST', '/admin/contenu/palmares/' . $pal['id'], ['saison' => '2025', 'lieu' => 'Gap', 'vainqueur' => 'Valgarde et Orange', 'detail_md' => '', 'document_id' => (string) $docId]);
        self::assertSame($docId, (int) $c->palmaresParId((int) $pal['id'])['document_id']);
        $this->req('POST', '/admin/contenu/palmares/' . $pal['id'] . '/supprimer');
        self::assertSame([], $c->palmares($id));

        $g = $this->app->service(GolfRepository::class)->trouverOuCreer('SALON');
        $this->req('POST', "/admin/contenu/golfs/$g", ['nom' => 'SALON', 'ville' => 'Salon-de-Provence', 'site_web' => 'https://www.golfecoledelair.fr', 'membre' => '1', 'ordre' => '0']);
        self::assertSame('Salon-de-Provence', $this->app->service(GolfRepository::class)->parId($g)['ville']);
        self::assertSame(302, $this->req('POST', '/admin/contenu/golfs', ['nom' => 'NOUVEAU', 'ville' => '', 'site_web' => '', 'membre' => '1', 'ordre' => '9'])->statut);
        self::assertStringContainsString('NOUVEAU', $this->req('GET', '/admin/contenu/golfs')->corps);

        self::assertSame(302, $this->req('POST', '/admin/contenu/organigramme', ['groupe' => 'bureau', 'fonction' => 'Président', 'prenom' => 'Guy', 'nom' => 'POMET', 'golf' => 'Valgarde', 'ordre' => '0'])->statut);
        self::assertStringContainsString('POMET', $this->req('GET', '/admin/contenu/organigramme')->corps);
        self::assertSame(302, $this->req('POST', '/admin/contenu/albums', ['titre' => 'Trophée 2025', 'annee' => '2025', 'url' => '/imagesTROPHEE_2025/', 'ordre' => '0'])->statut);
        self::assertStringContainsString('Trophée 2025', $this->req('GET', '/admin/contenu/albums')->corps);
        self::assertSame(200, $this->req('GET', '/admin/contenu/documents')->statut);
    }

    public function testAccesRefuseSansAdmin(): void
    {
        $m = $this->app->service(SerieRepository::class)->parCode('M')['id'];
        $cap = $this->app->service(UtilisateurRepository::class)->creer(['identifiant' => 'SALON', 'serie_id' => $m, 'hash_sha1' => sha1('x')]);
        $this->app->session()->set('utilisateur_id', $cap);
        self::assertSame(403, $this->req('GET', '/admin/contenu')->statut);
        self::assertSame(403, $this->req('POST', '/admin/contenu/pages', ['slug' => 'x', 'titre' => 'x', 'corps_md' => ''])->statut);
    }
}
