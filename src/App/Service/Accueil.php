<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\ActualiteRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\PageRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;

/**
 * Rassemble les données de la page d'accueil : une requête par section,
 * aucune requête par rencontre. Les textes éditoriaux absents sont rendus
 * par un repli dans le gabarit (`templates/site/accueil.php`).
 */
final class Accueil
{
    /** Pages éditoriales utilisées par l'accueil, dans l'ordre d'apparition. */
    public const SLUGS = ['accueil-accroche', 'accueil-esprit', 'accueil-encart',
        'format-interclubs', 'format-mixte', 'format-h1', 'format-individuelles', 'citation'];

    private const NB_RENCONTRES = 6;
    private const NB_LIGNES_CLASSEMENT = 5;
    private const NB_ACTUALITES = 3;

    public function __construct(private App $app) {}

    /**
     * @return array{saison:?array, series:list<array>, chiffres:array{golfs:int, equipes:int, journees:int, competitions:int},
     *   prochaine_journee:?array, classements:list<array>, prochaines_rencontres:list<array>,
     *   competitions:list<array>, actualites:list<array>, golfs:list<array>, pages:array<string, ?array>}
     */
    public function donnees(\DateTimeImmutable $aujourdhui): array
    {
        $ymd = $aujourdhui->format('Y-m-d');
        $saison = $this->app->service(SaisonRepository::class)->active();
        $saisonId = $saison === null ? 0 : (int) $saison['id'];

        $series = $this->app->service(SerieRepository::class)->toutes();
        $golfs = $this->app->service(GolfRepository::class)->membres();
        $journees = $saisonId === 0 ? [] : $this->app->service(JourneeRepository::class)->datesDistinctes($saisonId);
        $competitions = $this->competitions();

        return [
            'saison' => $saison,
            'chiffres' => [
                'golfs' => count($golfs),
                'equipes' => $saisonId === 0 ? 0 : $this->app->service(EquipeRepository::class)->compterEngagees($saisonId),
                'journees' => count($journees),
                'competitions' => count($competitions),
            ],
            'series' => $series,
            'prochaine_journee' => $this->prochaineJournee($saisonId, $ymd),
            'classements' => $this->classements($saisonId, $series),
            'prochaines_rencontres' => $saisonId === 0 ? []
                : $this->app->service(RencontreRepository::class)->aVenir($saisonId, $ymd, self::NB_RENCONTRES),
            'competitions' => $competitions,
            'actualites' => $this->app->service(ActualiteRepository::class)->publiees(self::NB_ACTUALITES),
            'golfs' => $golfs,
            'pages' => $this->pages(),
        ];
    }

    /** @return null|array{date:string, series:list<array{code:string, libelle:string, nb_rencontres:int, nb_divisions:int, nb_journees:int}>} */
    private function prochaineJournee(int $saisonId, string $ymd): ?array
    {
        if ($saisonId === 0) { return null; }
        $rencontres = $this->app->service(RencontreRepository::class);
        $date = $rencontres->premiereDateAVenir($saisonId, $ymd);
        if ($date === null) { return null; }
        $series = [];
        foreach ($rencontres->resumeParSerieALaDate($saisonId, $date) as $l) {
            $series[] = [
                'code' => (string) $l['code'],
                'libelle' => (string) $l['libelle'],
                'journee_numero' => $l['journee_numero'] === null ? null : (int) $l['journee_numero'],
                'journee_phase' => $l['journee_phase'] === null ? null : (string) $l['journee_phase'],
                'nb_rencontres' => (int) $l['nb_rencontres'],
                'nb_divisions' => (int) $l['nb_divisions'],
                'nb_journees' => (int) $l['nb_journees'],
            ];
        }
        return ['date' => $date, 'series' => $series];
    }

    /** Première division de chaque série, cinq premières lignes. @param list<array> $series
     *  @return list<array{serie:array, division:array, lignes:list<array>}> */
    private function classements(int $saisonId, array $series): array
    {
        if ($saisonId === 0) { return []; }
        $divisions = $this->app->service(DivisionRepository::class);
        $out = [];
        foreach ($series as $serie) {
            $division = $divisions->parSaisonEtSerie($saisonId, (int) $serie['id'])[0] ?? null;
            if ($division === null) { continue; }
            $lignes = $this->app->service(ClassementService::class)->pourDivision((int) $division['id']);
            $out[] = ['serie' => $serie, 'division' => $division, 'lignes' => array_slice($lignes, 0, self::NB_LIGNES_CLASSEMENT)];
        }
        return $out;
    }

    /** @return list<array> compétitions actives enrichies de leur dernier palmarès */
    private function competitions(): array
    {
        $repo = $this->app->service(CompetitionRepository::class);
        $out = [];
        foreach ($repo->toutes() as $c) {
            $c['dernier_palmares'] = $repo->dernierPalmares((int) $c['id']);
            $out[] = $c;
        }
        return $out;
    }

    /** @return array<string, ?array> slug => page ou null */
    private function pages(): array
    {
        $repo = $this->app->service(PageRepository::class);
        $out = [];
        foreach (self::SLUGS as $slug) { $out[$slug] = $repo->parSlug($slug); }
        return $out;
    }
}
