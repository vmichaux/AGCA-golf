<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use Agca\Domain\Controles;
use Agca\Domain\Score;
use Agca\Domain\Serie;
use PHPUnit\Framework\TestCase;

final class ControlesTest extends TestCase
{
    private static function j(string $nom, ?float $index, ?string $sexe = 'H'): array
    {
        return ['nom' => $nom, 'index' => $index, 'sexe' => $sexe];
    }

    private static function simple(array $rec, array $inv, ?string $res = 'G', ?Score $score = null): array
    {
        return ['type' => 'S', 'rec' => [$rec], 'inv' => [$inv], 'resultat' => $res, 'score' => $score];
    }

    public function testFeuilleCompleteEtConformeSansAlerte(): void
    {
        $parties = [self::simple(self::j('A', 12.0), self::j('B', 18.0))];
        self::assertSame([], Controles::verifier(Serie::h1(), $parties));
        self::assertSame([], Controles::verifier(Serie::mixte(), $parties));
    }

    public function testIndexHorsBornesMixteSeulement(): void
    {
        $parties = [self::simple(self::j('A', 25.0), self::j('B', 18.0))];
        self::assertSame(['INDEX_HORS_BORNES'], Controles::verifier(Serie::mixte(), $parties));
        self::assertSame([], Controles::verifier(Serie::h1(), $parties));
    }

    public function testUnJokerHommeEtUneJokerDameAutorises(): void
    {
        $parties = [
            self::simple(self::j('A', 9.0, 'H'), self::j('B', 18.0)),
            self::simple(self::j('C', 10.5, 'D'), self::j('D', 18.0)),
        ];
        self::assertSame([], Controles::verifier(Serie::mixte(), $parties));
    }

    public function testDeuxJokersHommesDansLaMemeEquipe(): void
    {
        $parties = [
            self::simple(self::j('A', 9.0, 'H'), self::j('B', 18.0)),
            self::simple(self::j('C', 10.0, 'H'), self::j('D', 18.0)),
        ];
        self::assertSame(['JOKER_H_MULTIPLE'], Controles::verifier(Serie::mixte(), $parties));
    }

    public function testLeMemeJokerEnSimpleEtEnDoubleCompteUneFois(): void
    {
        $parties = [
            self::simple(self::j('A', 9.0, 'H'), self::j('B', 18.0)),
            ['type' => 'D', 'rec' => [self::j('A', 9.0, 'H'), self::j('E', 15.0, 'D')],
             'inv' => [self::j('B', 18.0), self::j('F', 16.0)], 'resultat' => 'P', 'score' => null],
        ];
        self::assertSame([], Controles::verifier(Serie::mixte(), $parties));
    }

    public function testJokersComptesParEquipe(): void
    {
        $parties = [
            self::simple(self::j('A', 9.0, 'H'), self::j('B', 9.5, 'H')),
        ];
        self::assertSame([], Controles::verifier(Serie::mixte(), $parties));
    }

    public function testJokerDeSexeInconnuEstHorsBornes(): void
    {
        $parties = [self::simple(self::j('A', 9.0, null), self::j('B', 18.0))];
        self::assertSame(['INDEX_HORS_BORNES'], Controles::verifier(Serie::mixte(), $parties));
    }

    public function testFeuilleIncomplete(): void
    {
        self::assertSame(['FEUILLE_INCOMPLETE'], Controles::verifier(Serie::h1(), [self::simple(self::j('A', 5.0), self::j('', 5.0))]));
        self::assertSame(['FEUILLE_INCOMPLETE'], Controles::verifier(Serie::h1(), [self::simple(self::j('A', 5.0), self::j('B', 5.0), null)]));
        self::assertSame(['FEUILLE_INCOMPLETE'], Controles::verifier(Serie::h1(), [self::simple(self::j('A', null), self::j('B', 5.0))]));
    }

    public function testScoreIncoherent(): void
    {
        $parties = [self::simple(self::j('A', 5.0), self::j('B', 5.0), 'G', Score::depuisTexte('AS'))];
        self::assertSame(['SCORE_INCOHERENT'], Controles::verifier(Serie::h1(), $parties));
    }

    public function testCodesUniquesEtLibelles(): void
    {
        $parties = [
            self::simple(self::j('A', 25.0), self::j('B', 30.0)),
            self::simple(self::j('C', 25.0), self::j('D', 18.0)),
        ];
        self::assertSame(['INDEX_HORS_BORNES'], Controles::verifier(Serie::mixte(), $parties));
        self::assertArrayHasKey('INDEX_HORS_BORNES', Controles::LIBELLES);
    }
}
