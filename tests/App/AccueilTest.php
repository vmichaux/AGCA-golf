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
    private int $divisionM = 0;

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
            if ($code === 'M') { $this->divisionM = (int) $div['id']; }
        }

        // Feuille enregistrée sur la première rencontre jouée : l'invité l'emporte largement.
        $rencontres = $this->app->service(RencontreRepository::class);
        $premiere = $rencontres->parDivision($this->divisionM)[0];
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
        self::assertSame(1, $p['series'][0]['nb_journees']);
        self::assertSame(2, $p['series'][0]['journee_numero']);
        self::assertSame('aller', $p['series'][0]['journee_phase']);
        self::assertStringContainsString('Mixte 2e série — 2e journée aller, 2 rencontres dans 1 poule', $this->rendu());
    }

    /**
     * Une rencontre reportée sur la date d'une autre journée : la date porte deux journées,
     * le numéro ne la caractérise plus et le libellé doit rester neutre.
     */
    public function testProchaineJourneeSansNumeroQuandDeuxJourneesLeMemeJour(): void
    {
        $rencontres = $this->app->service(RencontreRepository::class);
        $troisieme = null;
        foreach ($rencontres->parDivision($this->divisionM) as $r) {
            if ((int) $r['journee_numero'] === 3 && $r['journee_phase'] === 'aller') { $troisieme = $r; break; }
        }
        self::assertNotNull($troisieme);
        $rencontres->mettreAJourDate((int) $troisieme['id'], '2026-09-26', true);

        $p = $this->donnees()['prochaine_journee'];
        self::assertSame('2026-09-26', $p['date']);
        self::assertSame(2, $p['series'][0]['nb_journees']);
        self::assertSame(3, $p['series'][0]['nb_rencontres']);

        $html = $this->rendu();
        self::assertStringContainsString('Mixte 2e série — 3 rencontres dans 1 poule', $html);
        self::assertStringNotContainsString('journée aller', $html);
        self::assertStringNotContainsString('journée retour', $html);
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

    /**
     * Rend le gabarit avec les données de la date de test : `GET /` utilise la date du jour,
     * ce qui rendrait toute assertion sur le bandeau « Prochaine journée » dépendante du calendrier.
     */
    private function rendu(): string
    {
        $v = $this->app->view();
        $v->partager('utilisateur', null);
        $v->partager('flashs', []);
        $v->partager('csrf', 'test');
        $v->partager('menuPages', []);
        return $v->rendre('site/accueil', $this->donnees() + ['titre' => 'AGCA', 'description' => '', 'accroche_repli' => 'Accroche.']);
    }

    private function html(): string
    {
        $rep = $this->app->executer(new Request('GET', '/', [], [], '127.0.0.1'));
        self::assertSame(200, $rep->statut);
        return $rep->corps;
    }

    /** Sans actualité publiée, la section n'est pas rendue : les articles de la maquette sont des exemples. */
    public function testSectionActualitesAbsenteSansActualitePubliee(): void
    {
        $actus = $this->app->service(ActualiteRepository::class);
        foreach ($actus->toutes() as $a) { $actus->modifier((int) $a['id'], ['publie' => 0]); }
        self::assertSame([], $this->donnees()['actualites']);

        $html = $this->html();
        self::assertStringNotContainsString('La vie de l\'association', $html);
        self::assertStringNotContainsString('Toutes les actualités', $html);
        self::assertStringNotContainsString('Le Master 2025 à Gap', $html);
        self::assertStringNotContainsString('La saison 2026-27 démarre le 26 septembre', $html);
        // les autres replis restent en place
        self::assertStringContainsString('Découvrir de nouveaux parcours', $html);
        self::assertStringContainsString('Cinq rendez-vous dans la saison', $html);
        self::assertStringContainsString('De Gap à Sainte-Maxime', $html);
    }

    /** Sans compétition active, la section n'est pas rendue : aucun repli codé en dur. */
    public function testSectionCompetitionsAbsenteSansCompetition(): void
    {
        $comp = $this->app->service(CompetitionRepository::class);
        foreach ($comp->toutes(false) as $c) { $comp->modifier((int) $c['id'], ['actif' => 0]); }
        self::assertSame([], $this->donnees()['competitions']);

        $html = $this->html();
        self::assertStringNotContainsString('Cinq rendez-vous dans la saison', $html);
        self::assertStringNotContainsString('id="competitions"', $html);
        // les autres replis restent en place
        self::assertStringContainsString('Découvrir de nouveaux parcours', $html);
        self::assertStringContainsString('De Gap à Sainte-Maxime', $html);
    }

    /** Sans golf membre, la section n'est pas rendue : aucun repli codé en dur. */
    public function testSectionGolfsAbsenteSansGolfMembre(): void
    {
        $golfs = $this->app->service(GolfRepository::class);
        foreach ($golfs->tous() as $g) { $golfs->modifier((int) $g['id'], ['membre' => 0]); }
        self::assertSame([], $this->donnees()['golfs']);

        $html = $this->html();
        self::assertStringNotContainsString('De Gap à Sainte-Maxime', $html);
        self::assertStringNotContainsString('grille-golfs', $html);
        // les autres replis restent en place
        self::assertStringContainsString('Découvrir de nouveaux parcours', $html);
        self::assertStringContainsString('Cinq rendez-vous dans la saison', $html);
    }

    public function testPageAccueil(): void
    {
        $html = $this->html();
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
