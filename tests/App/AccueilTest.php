<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Http\Request;
use Agca\App\Repository\ActualiteRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\PageRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Service\Accueil;
use Agca\App\Service\CreationSaison;
use Agca\Domain\Markdown;

final class AccueilTest extends DbTestCase
{
    private const AUJOURDHUI = '2026-09-15';
    private const CITATION = 'Le sport relie les gens, construit des amitiés et avant tout, vous permet de vous faire plaisir.';
    private const ACCROCHE = 'Le golf par équipes entre clubs amis, de septembre à mai, dans l\'esprit de Saint Andrews.';

    private App $app;
    private array $admin = ['id' => 1, 'identifiant' => 'ADMIN', 'est_admin' => 1];
    private int $saisonId;
    /** @var array<string, int> nom d'équipe => identifiant */
    private array $equipes = [];
    private string $gagnante = '';

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $this->app = new App($this->config());
        $this->saison();
        $this->contenu();
    }

    /** Deux séries, une division de 4 équipes par série, une feuille enregistrée. */
    private function saison(): void
    {
        $creation = $this->app->service(CreationSaison::class);
        $r = $creation->creerSaison('2026-27', '2026-09-01', '2027-06-30', [
            'M' => ['aller' => ['2026-09-05', '2026-09-26', '2026-10-17'], 'retour' => ['2026-11-07', '2026-11-28', '2026-12-19']],
            'H1' => ['aller' => ['2026-09-12', '2026-10-03', '2026-10-24'], 'retour' => ['2026-11-14', '2026-12-05', '2027-01-09']],
        ], $this->admin);
        self::assertSame([], $r['erreurs']);
        $this->saisonId = (int) $r['id'];

        $golfs = $this->app->service(GolfRepository::class);
        $valgarde = $golfs->trouverOuCreer('VALGARDE', 'La Crau');
        $salon = $golfs->trouverOuCreer('SALON', 'Salon-de-Provence');
        $ancien = $golfs->trouverOuCreer('ANCIEN', 'Nulle part');
        $golfs->modifier($valgarde, ['site_web' => 'https://www.golfvalgarde.com', 'ordre' => 1]);
        $golfs->modifier($ancien, ['membre' => 0]);

        $series = $this->app->service(SerieRepository::class);
        $equipes = $this->app->service(EquipeRepository::class);
        $poules = [
            'M' => ['libelle' => 'DIV2/POULE B', 'noms' => ['SAINT-MARTIN-2', 'LUBERON-1', 'SALON', 'FREGATE']],
            'H1' => ['libelle' => 'Division 1', 'noms' => ['VALGARDE-1', 'SALON-H1', 'ORANGE', 'VICTORIA']],
        ];
        foreach ($poules as $code => $poule) {
            $serieId = (int) $series->parCode($code)['id'];
            $positions = [];
            foreach ($poule['noms'] as $i => $nom) {
                $id = $equipes->creer(['golf_id' => $i % 2 === 0 ? $valgarde : $salon, 'serie_id' => $serieId, 'nom' => $nom]);
                $this->equipes[$nom] = $id;
                $positions[$i + 1] = $id;
            }
            $div = $creation->ajouterDivision($this->saisonId, $serieId, $poule['libelle'], $positions, $this->admin);
            self::assertSame([], $div['erreurs']);
        }

        // Feuille enregistrée sur la première rencontre jouée : l'invité l'emporte largement.
        $rencontres = $this->app->service(RencontreRepository::class);
        $premiere = $rencontres->parDivision((int) $this->app->service(\Agca\App\Repository\DivisionRepository::class)
            ->parSaisonEtSerie($this->saisonId, (int) $series->parCode('M')['id'])[0]['id'])[0];
        $rencontres->mettreAJourResultat((int) $premiere['id'], ['statut' => 'enregistree', 'total_pour' => 8, 'total_contre' => 22,
            'pts_rencontre_pour' => 0, 'pts_rencontre_contre' => 2, 'bonus_invite' => 1]);
        $this->gagnante = (string) $premiere['invite_nom'];
    }

    /** Deux compétitions dont une avec palmarès, deux actualités dont une publiée, deux pages. */
    private function contenu(): void
    {
        $comp = $this->app->service(CompetitionRepository::class);
        $master = $comp->creer(['code' => 'master', 'nom' => 'Le Master', 'accroche' => 'Clôture de saison', 'formule' => 'Réservé aux équipes premières de chaque division.',
            'corps_md' => '', 'corps_html' => '', 'ordre' => 1, 'actif' => 1]);
        $comp->creer(['code' => 'challenge', 'nom' => 'Le Challenge', 'accroche' => 'Ouverture de saison', 'formule' => 'Par équipes de club.',
            'corps_md' => '', 'corps_html' => '', 'ordre' => 2, 'actif' => 1]);
        $comp->ajouterPalmares(['competition_id' => $master, 'saison' => '2024', 'lieu' => 'Valcros', 'vainqueur' => 'Orange', 'detail_md' => null, 'detail_html' => null, 'document_id' => null, 'ordre' => 0]);
        $comp->ajouterPalmares(['competition_id' => $master, 'saison' => '2025', 'lieu' => 'Gap', 'vainqueur' => 'Valgarde', 'detail_md' => null, 'detail_html' => null, 'document_id' => null, 'ordre' => 0]);

        $actus = $this->app->service(ActualiteRepository::class);
        $actus->creer(['titre' => 'Le Master 2025 à Gap', 'slug' => 'master-2025-gap', 'date_publication' => '2026-09-01', 'resume' => 'Journée de clôture.',
            'corps_md' => 'Compte rendu.', 'corps_html' => Markdown::rendre('Compte rendu.'), 'publie' => 1, 'modifie_par' => null]);
        $actus->creer(['titre' => 'Brouillon interne', 'slug' => 'brouillon-interne', 'date_publication' => '2026-09-10', 'resume' => null,
            'corps_md' => 'Rien.', 'corps_html' => Markdown::rendre('Rien.'), 'publie' => 0, 'modifie_par' => null]);

        $pages = $this->app->service(PageRepository::class);
        foreach ([['accueil-accroche', 'Accroche', self::ACCROCHE], ['citation', 'Citation', '« ' . self::CITATION . ' »']] as [$slug, $titre, $md]) {
            $pages->creer(['slug' => $slug, 'titre' => $titre, 'corps_md' => $md, 'corps_html' => Markdown::rendre($md), 'systeme' => 1, 'dans_menu' => 0, 'ordre' => 0, 'modifie_par' => null]);
        }
    }

    private function donnees(): array
    {
        return $this->app->service(Accueil::class)->donnees(new \DateTimeImmutable(self::AUJOURDHUI));
    }

    public function testChiffresCles(): void
    {
        self::assertSame(['golfs' => 2, 'equipes' => 8, 'journees' => 12, 'competitions' => 2], $this->donnees()['chiffres']);
    }

    public function testProchaineJournee(): void
    {
        $p = $this->donnees()['prochaine_journee'];
        self::assertNotNull($p);
        self::assertSame('2026-09-26', $p['date']);
        self::assertCount(1, $p['series']);
        self::assertSame('M', $p['series'][0]['code']);
        self::assertSame('Mixte 2e série', $p['series'][0]['libelle']);
        self::assertSame(2, $p['series'][0]['nb_rencontres']);
        self::assertSame(1, $p['series'][0]['nb_divisions']);
    }

    public function testClassementsParSerie(): void
    {
        $c = $this->donnees()['classements'];
        self::assertCount(2, $c);
        self::assertSame(['M', 'H1'], array_column(array_column($c, 'serie'), 'code'));
        self::assertSame('DIV2/POULE B', $c[0]['division']['libelle']);
        self::assertLessThanOrEqual(5, count($c[0]['lignes']));
        self::assertSame($this->gagnante, $c[0]['lignes'][0]['nom']);
        self::assertSame(1, $c[0]['lignes'][0]['rang']);
    }

    public function testProchainesRencontres(): void
    {
        $rencontres = $this->donnees()['prochaines_rencontres'];
        self::assertLessThanOrEqual(6, count($rencontres));
        self::assertNotSame([], $rencontres);
        $dates = array_column($rencontres, 'date_reelle');
        self::assertSame('2026-09-26', $dates[0]);
        $triees = $dates; sort($triees);
        self::assertSame($triees, $dates);
        foreach ($rencontres as $r) {
            self::assertSame('a_jouer', $r['statut']);
            self::assertGreaterThanOrEqual(self::AUJOURDHUI, $r['date_reelle']);
            self::assertNotSame('', (string) $r['recevant_nom']);
            self::assertNotSame('', (string) $r['invite_nom']);
            self::assertNotSame('', (string) $r['division_libelle']);
            self::assertNotSame('', (string) $r['serie_code']);
        }
    }

    public function testCompetitionsActualitesGolfsEtPages(): void
    {
        $d = $this->donnees();
        self::assertSame(['Le Master', 'Le Challenge'], array_column($d['competitions'], 'nom'));
        self::assertSame('2025', $d['competitions'][0]['dernier_palmares']['saison']);
        self::assertSame('Valgarde', $d['competitions'][0]['dernier_palmares']['vainqueur']);
        self::assertNull($d['competitions'][1]['dernier_palmares']);
        self::assertSame(['Le Master 2025 à Gap'], array_column($d['actualites'], 'titre'));
        self::assertSame(['SALON', 'VALGARDE'], array_column($d['golfs'], 'nom'));
        self::assertStringContainsString(self::CITATION, $d['pages']['citation']['corps_html']);
        self::assertStringContainsString('septembre à mai', $d['pages']['accueil-accroche']['corps_html']);
        self::assertNull($d['pages']['accueil-esprit']);
        self::assertArrayHasKey('format-interclubs', $d['pages']);
    }

    public function testPageAccueil(): void
    {
        $rep = $this->app->executer(new Request('GET', '/', [], [], '127.0.0.1'));
        self::assertSame(200, $rep->statut);
        $html = $rep->corps;
        self::assertStringContainsString('Association des Golfs de la Coupe de l\'Amitié', $html);
        self::assertStringContainsString('DIV2/POULE B', $html);
        self::assertStringContainsString(self::CITATION, $html);
        self::assertStringContainsString('href="https://www.ffgolf.org/"', $html);
        self::assertStringContainsString('Le Master 2025 à Gap', $html);
        self::assertStringNotContainsString('Brouillon interne', $html);
        self::assertStringContainsString('SAINT-MARTIN-2', $html);
        self::assertStringContainsString('Le Master', $html);
        self::assertStringContainsString('href="/competitions/master"', $html);
        self::assertStringContainsString('class="chiffres"', $html);
        self::assertStringContainsString('class="entete"', $html);
    }
}
