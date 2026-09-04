<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\ActualiteRepository;
use Agca\App\Repository\AlbumRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\DocumentRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\OrganigrammeRepository;
use Agca\App\Repository\PageRepository;
use Agca\Domain\Markdown;

/** Tests de fumée des pages publiques : chaque route du site répond et affiche ses données. */
final class SitePagesTest extends DbTestCase
{
    private App $app;
    private int $actualiteId = 0;
    private int $brouillonId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $this->app = new App($this->config());
        $this->contenu();
    }

    private function contenu(): void
    {
        $comp = $this->app->service(CompetitionRepository::class);
        $master = $comp->creer(['code' => 'master', 'nom' => 'Le Master', 'accroche' => 'Clôture de saison',
            'formule' => 'Réservé aux équipes premières de chaque division.',
            'corps_md' => 'Le Master clôture la saison.', 'corps_html' => Markdown::rendre('Le Master clôture la saison.'), 'ordre' => 1, 'actif' => 1]);
        $comp->creer(['code' => 'challenge', 'nom' => 'Le Challenge', 'accroche' => 'Ouverture de saison',
            'formule' => 'Par équipes de club.', 'corps_md' => '', 'corps_html' => '', 'ordre' => 2, 'actif' => 1]);

        $documents = $this->app->service(DocumentRepository::class);
        $reglement = $documents->creer(['titre' => 'Résultats du Master 2025', 'fichier' => '2025-10-11-master-2025-a1b2c3.pdf',
            'type_mime' => 'application/pdf', 'taille' => 204800, 'categorie' => 'palmares', 'televerse_par' => null]);
        $statutsPdf = $documents->creer(['titre' => 'Statuts de l\'association', 'fichier' => '2024-01-05-statuts-d4e5f6.pdf',
            'type_mime' => 'application/pdf', 'taille' => 51200, 'categorie' => 'statuts', 'televerse_par' => null]);

        $comp->ajouterPalmares(['competition_id' => $master, 'saison' => '2024', 'lieu' => 'Valcros', 'vainqueur' => 'Orange',
            'detail_md' => null, 'detail_html' => null, 'document_id' => null, 'ordre' => 0]);
        $comp->ajouterPalmares(['competition_id' => $master, 'saison' => '2025', 'lieu' => 'Gap', 'vainqueur' => 'Valgarde',
            'detail_md' => 'Le temps était excellent.', 'detail_html' => Markdown::rendre('Le temps était excellent.'),
            'document_id' => $reglement, 'ordre' => 0]);

        $golfs = $this->app->service(GolfRepository::class);
        $golfs->modifier($golfs->trouverOuCreer('VALGARDE', 'La Crau'), ['site_web' => 'www.golfvalgarde.com', 'ordre' => 1]);
        $golfs->modifier($golfs->trouverOuCreer('SALON', 'Salon-de-Provence'), ['ordre' => 2]);
        $golfs->modifier($golfs->trouverOuCreer('ANCIEN', 'Nulle part'), ['membre' => 0]);

        $this->app->service(AlbumRepository::class)->creer(['titre' => 'Trophée 2025', 'annee' => 2025, 'url' => '/imagesTROPHEE_2025/', 'ordre' => 1]);

        $actus = $this->app->service(ActualiteRepository::class);
        $this->actualiteId = $actus->creer(['titre' => 'Le Master 2025 à Gap', 'slug' => 'master-2025-gap', 'date_publication' => '2026-09-01',
            'resume' => 'Journée de clôture.', 'corps_md' => 'Compte rendu de la journée.',
            'corps_html' => Markdown::rendre('Compte rendu de la journée.'), 'publie' => 1, 'modifie_par' => null]);
        $this->brouillonId = $actus->creer(['titre' => 'Brouillon interne', 'slug' => 'brouillon-interne', 'date_publication' => '2026-09-10',
            'resume' => null, 'corps_md' => 'Rien.', 'corps_html' => Markdown::rendre('Rien.'), 'publie' => 0, 'modifie_par' => null]);

        $pages = $this->app->service(PageRepository::class);
        $statuts = $pages->creer(['slug' => 'statuts', 'titre' => 'Statuts', 'corps_md' => 'Les statuts de l\'association.',
            'corps_html' => Markdown::rendre('Les statuts de l\'association.'), 'systeme' => 1, 'dans_menu' => 1, 'ordre' => 1, 'modifie_par' => null]);
        $pages->definirDocuments($statuts, [$statutsPdf]);
        $pages->creer(['slug' => 'mentions-legales', 'titre' => 'Mentions légales', 'corps_md' => 'Politique de confidentialité.',
            'corps_html' => Markdown::rendre('Politique de confidentialité.'), 'systeme' => 1, 'dans_menu' => 0, 'ordre' => 9, 'modifie_par' => null]);
        $pages->creer(['slug' => 'coupe-anniversaire', 'titre' => 'Coupe anniversaire', 'corps_md' => 'Une page libre.',
            'corps_html' => Markdown::rendre('Une page libre.'), 'systeme' => 0, 'dans_menu' => 1, 'ordre' => 5, 'modifie_par' => null]);
        $pages->creer(['slug' => 'citation', 'titre' => 'Citation', 'corps_md' => '« Le sport relie les gens. »',
            'corps_html' => Markdown::rendre('« Le sport relie les gens. »'), 'systeme' => 1, 'dans_menu' => 0, 'ordre' => 0, 'modifie_par' => null]);

        $organi = $this->app->service(OrganigrammeRepository::class);
        $organi->creer(['groupe' => 'bureau', 'fonction' => 'Président', 'prenom' => 'Guy', 'nom' => 'POMET', 'golf' => 'Valgarde', 'ordre' => 1]);
        $organi->creer(['groupe' => 'ca', 'fonction' => 'Membre', 'prenom' => 'Robert', 'nom' => 'MICHAUX', 'golf' => 'Aix-en-Provence', 'ordre' => 1]);
    }

    /** @param array<string, mixed> $post */
    private function req(string $methode, string $chemin, array $post = []): Response
    {
        $get = [];
        $qs = parse_url($chemin, PHP_URL_QUERY);
        if ($qs) { parse_str($qs, $get); }
        return $this->app->executer(new Request($methode, parse_url($chemin, PHP_URL_PATH) ?: '/', $get, $post, '127.0.0.1'));
    }

    private function get(string $chemin): string
    {
        $rep = $this->req('GET', $chemin);
        self::assertSame(200, $rep->statut, $chemin);
        return $rep->corps;
    }

    private function statut(string $chemin): int
    {
        return $this->req('GET', $chemin)->statut;
    }

    public function testListeDesCompetitions(): void
    {
        $html = $this->get('/competitions');
        self::assertStringContainsString('Le Master', $html);
        self::assertStringContainsString('Le Challenge', $html);
        self::assertStringContainsString('href="/competitions/master"', $html);
        self::assertStringContainsString('Réservé aux équipes premières', $html);
        self::assertStringContainsString('Valgarde', $html, 'dernier vainqueur');
    }

    public function testFicheCompetitionAvecPalmares(): void
    {
        $html = $this->get('/competitions/master');
        self::assertStringContainsString('Le Master', $html);
        self::assertStringContainsString('Le Master clôture la saison.', $html);
        // palmarès : la saison la plus récente d'abord
        self::assertStringContainsString('2025', $html);
        self::assertStringContainsString('Gap', $html);
        self::assertStringContainsString('Valgarde', $html);
        self::assertStringContainsString('Le temps était excellent.', $html);
        self::assertStringContainsString('href="/documents/2025-10-11-master-2025-a1b2c3.pdf"', $html);
        self::assertStringContainsString('Résultats du Master 2025', $html);
        self::assertLessThan(strpos($html, 'Orange'), strpos($html, 'Valgarde'));
    }

    public function testCompetitionInconnue(): void
    {
        self::assertSame(404, $this->statut('/competitions/inexistante'));
    }

    public function testGolfsMembres(): void
    {
        $html = $this->get('/golfs');
        self::assertStringContainsString('VALGARDE', $html);
        self::assertStringContainsString('La Crau', $html);
        self::assertStringContainsString('https://www.golfvalgarde.com', $html);
        self::assertStringContainsString('SALON', $html);
        self::assertStringNotContainsString('ANCIEN', $html);
    }

    public function testAlbumsPhoto(): void
    {
        $html = $this->get('/photos');
        self::assertStringContainsString('Trophée 2025', $html);
        self::assertStringContainsString('href="/imagesTROPHEE_2025/"', $html);
        self::assertStringContainsString('target="_blank"', $html);
        self::assertStringContainsString('rel="noopener"', $html);
    }

    public function testListeDesActualites(): void
    {
        $html = $this->get('/actualites');
        self::assertStringContainsString('Le Master 2025 à Gap', $html);
        self::assertStringContainsString('href="/actualites/' . $this->actualiteId . '-master-2025-gap"', $html);
        self::assertStringNotContainsString('Brouillon interne', $html);
    }

    public function testPaginationDesActualites(): void
    {
        $actus = $this->app->service(ActualiteRepository::class);
        for ($i = 1; $i <= 11; $i++) {
            $actus->creer(['titre' => 'Actualité ' . $i, 'slug' => 'actualite-' . $i, 'date_publication' => sprintf('2026-08-%02d', $i),
                'resume' => null, 'corps_md' => 'Texte.', 'corps_html' => Markdown::rendre('Texte.'), 'publie' => 1, 'modifie_par' => null]);
        }
        $page1 = $this->get('/actualites');
        self::assertSame(10, substr_count($page1, '<article class="actu">'));
        self::assertStringContainsString('href="/actualites?page=2"', $page1);
        self::assertStringContainsString('Plus anciennes', $page1);
        self::assertStringNotContainsString('Plus récentes', $page1);

        $page2 = $this->get('/actualites?page=2');
        self::assertSame(2, substr_count($page2, '<article class="actu">'));
        self::assertStringContainsString('Actualité 1', $page2);
        self::assertStringContainsString('Plus récentes', $page2);
        self::assertStringNotContainsString('Plus anciennes', $page2);
    }

    public function testArticle(): void
    {
        $html = $this->get('/actualites/' . $this->actualiteId . '-master-2025-gap');
        self::assertStringContainsString('Le Master 2025 à Gap', $html);
        self::assertStringContainsString('Compte rendu de la journée.', $html);
    }

    public function testArticleRedirigeVersSonUrlCanonique(): void
    {
        $rep = $this->req('GET', '/actualites/' . $this->actualiteId . '-mauvais-slug');
        self::assertSame(301, $rep->statut);
        self::assertSame('/actualites/' . $this->actualiteId . '-master-2025-gap', $rep->entetes['Location']);
    }

    public function testArticleBrouillonEtInconnu(): void
    {
        self::assertSame(404, $this->statut('/actualites/' . $this->brouillonId . '-brouillon-interne'));
        self::assertSame(404, $this->statut('/actualites/999999-inexistante'));
        self::assertSame(404, $this->statut('/actualites/pas-un-identifiant'));
    }

    public function testOrganigramme(): void
    {
        $html = $this->get('/association/organigramme');
        self::assertStringContainsString('Président', $html);
        self::assertStringContainsString('POMET', $html);
        self::assertStringContainsString('MICHAUX', $html);
        self::assertStringContainsString('Bureau', $html);
        self::assertStringContainsString('administration', $html);
    }

    public function testPageDeTexteAvecDocument(): void
    {
        $html = $this->get('/page/statuts');
        self::assertStringContainsString('Statuts', $html);
        self::assertStringContainsString('Les statuts de l\'association.', $html);
        self::assertStringContainsString('href="/documents/2024-01-05-statuts-d4e5f6.pdf"', $html);
        self::assertStringContainsString('50 Ko', $html);
    }

    public function testPageLibre(): void
    {
        self::assertStringContainsString('Une page libre.', $this->get('/page/coupe-anniversaire'));
    }

    public function testMentionsLegales(): void
    {
        $html = $this->get('/mentions-legales');
        self::assertStringContainsString('Mentions légales', $html);
        self::assertStringContainsString('Politique de confidentialité.', $html);
    }

    /** Les fragments de l'accueil ne sont pas des pages : ils ne s'affichent pas seuls. */
    public function testFragmentsEtPagesInconnues(): void
    {
        foreach (['citation', 'accueil-accroche', 'accueil-esprit', 'format-mixte', 'inexistante'] as $slug) {
            self::assertSame(404, $this->statut('/page/' . $slug), $slug);
        }
    }

    public function testFormulaireDeContact(): void
    {
        $html = $this->get('/contact');
        self::assertStringContainsString('<form method="post" action="/contact"', $html);
        self::assertStringContainsString('name="_csrf"', $html);
        self::assertStringContainsString('name="site_web"', $html);
        self::assertStringContainsString('class="piege"', $html);
    }

    public function testContactSansJetonCsrf(): void
    {
        $rep = $this->req('POST', '/contact', ['nom' => 'Jean', 'email' => 'jean@example.org', 'objet' => 'Bonjour',
            'message' => 'Un message assez long pour passer.', 'site_web' => '']);
        self::assertSame(400, $rep->statut);
    }

    public function testContactValidePuisErreurs(): void
    {
        $this->app->session()->demarrer();
        $csrf = $this->app->session()->csrf();
        $rep = $this->req('POST', '/contact', ['_csrf' => $csrf, 'nom' => 'Jean Dupont', 'email' => 'jean@example.org',
            'objet' => 'Engager une équipe', 'message' => 'Notre club souhaite engager une équipe.', 'site_web' => '']);
        self::assertSame(302, $rep->statut);
        self::assertSame('/contact', $rep->entetes['Location']);

        $rep = $this->req('POST', '/contact', ['_csrf' => $csrf, 'nom' => 'Jean Dupont', 'email' => 'pas-un-email',
            'objet' => 'Engager une équipe', 'message' => 'Notre club souhaite engager une équipe.', 'site_web' => '']);
        self::assertSame(422, $rep->statut);
        self::assertStringContainsString('value="pas-un-email"', $rep->corps, 'les valeurs saisies sont conservées');
        self::assertStringContainsString('Notre club souhaite engager une équipe.', $rep->corps);
    }
}
