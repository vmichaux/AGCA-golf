<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Repository\AlbumRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\PageRepository;
use Agca\App\Service\ContenuInitial;

final class ContenuInitialTest extends DbTestCase
{
    public function testImportEtIdempotence(): void
    {
        $app = new App($this->config());
        $golfs = $app->service(GolfRepository::class);
        $v = $golfs->trouverOuCreer('VALGARDE'); $golfs->modifier($v, ['ville' => 'Ville saisie']);
        $r = $app->service(ContenuInitial::class)->executer(null);
        self::assertNotSame([], $r['rapport']);
        $pages = $app->service(PageRepository::class);
        foreach (['accueil-accroche', 'accueil-esprit', 'accueil-encart', 'format-interclubs', 'format-mixte', 'format-h1', 'format-individuelles', 'citation', 'statuts', 'assemblees-generales', 'voyage', 'mentions-legales'] as $slug) {
            self::assertNotNull($pages->parSlug($slug), $slug);
            self::assertSame(1, (int) $pages->parSlug($slug)['systeme']);
        }
        self::assertStringContainsString('Saint Andrews', $pages->parSlug('accueil-accroche')['corps_html']);
        self::assertStringContainsString('<a href="/contact"', $pages->parSlug('accueil-encart')['corps_html']);
        self::assertStringContainsString('Cannes-Mougins', $pages->parSlug('mentions-legales')['corps_html']);
        $c = $app->service(CompetitionRepository::class);
        self::assertSame(['challenge', 'tournoi', 'trophee', 'master', 'coupe-capitaines'], array_column($c->toutes(), 'code'));
        $master = $c->parCode('master');
        self::assertSame(['2025', 'Gap'], [$c->dernierPalmares((int) $master['id'])['saison'], $c->dernierPalmares((int) $master['id'])['lieu']]);
        self::assertStringContainsString('Valgarde', $c->dernierPalmares((int) $master['id'])['vainqueur']);
        self::assertNotEmpty($c->dernierPalmares((int) $master['id'])['detail_html']);
        self::assertSame('Ville saisie', $golfs->parId($v)['ville']);
        self::assertSame('https://www.golf-valgarde.com', $golfs->parId($v)['site_web']);
        self::assertSame('Aix-en-Provence', $golfs->parNom('AIX-EN-PROVENCE')['ville']);
        self::assertCount(19, $golfs->membres());
        // Note : la spec (§5) et le brief de la tâche annoncent « 24 dossiers listés dans la spec », mais la
        // spec ne fait que citer deux exemples de nommage (imagesCHALLENGE2018 … imagesanniversaire35ans) sans
        // jamais énumérer les 24 dossiers. Faute d'une liste faisant autorité, db/contenu/initial.php reconstruit
        // les albums UNIQUEMENT à partir des éditions réellement attestées dans db/contenu/sources/*.txt (une
        // galerie par en-tête d'édition trouvé dans challenge.txt, master.txt, tournoi.txt, trophee.txt), plus
        // l'album anniversaire cité par la spec — soit 29 albums, tous traçables à une source. Aucun album
        // « Voyage » n'est créé (aucune édition n'est documentée). Voir task-5-report.md.
        self::assertCount(29, $app->service(AlbumRepository::class)->tous());
        // idempotence
        $pages->modifier((int) $pages->parSlug('voyage')['id'], ['corps_md' => 'modifié', 'corps_html' => '<p>modifié</p>']);
        $r2 = $app->service(ContenuInitial::class)->executer(null);
        self::assertSame('<p>modifié</p>', $pages->parSlug('voyage')['corps_html']);
        self::assertCount(29, $app->service(AlbumRepository::class)->tous());
        self::assertCount(1, $c->palmares((int) $master['id']));
        self::assertCount(19, $golfs->membres());
    }
}
