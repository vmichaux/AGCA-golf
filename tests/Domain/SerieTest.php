<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use Agca\Domain\Serie;
use PHPUnit\Framework\TestCase;

final class SerieTest extends TestCase
{
    public function testMixteA15PartiesEnBlocsSSD(): void
    {
        $s = Serie::mixte();
        self::assertSame(15, $s->nbParties());
        self::assertSame('SSDSSDSSDSSDSSD', $s->structureTexte());
        self::assertSame([2, 0], $s->pointsPartie('G'));
        self::assertSame([1, 1], $s->pointsPartie('N'));
        self::assertSame([0, 2], $s->pointsPartie('P'));
        self::assertSame([0, 0], $s->pointsPartie(null));
        self::assertTrue($s->mixte);
        self::assertSame(11.5, $s->indexMin);
        self::assertSame(22.0, $s->indexMax);
    }

    public function testH1A5PartiesDoubleEnPremier(): void
    {
        $s = Serie::h1();
        self::assertSame('DSSSS', $s->structureTexte());
        self::assertSame([3, 1], $s->pointsPartie('G'));
        self::assertSame([2, 2], $s->pointsPartie('N'));
        self::assertSame([1, 3], $s->pointsPartie('P'));
        self::assertNull($s->indexMax);
        self::assertFalse($s->mixte);
    }

    public function testDepuisLigneBase(): void
    {
        $s = Serie::depuisLigne([
            'code' => 'M', 'libelle' => 'Mixte 2e série', 'structure' => 'SSD',
            'pts_gagne' => '2', 'pts_nul' => '1', 'pts_perdu' => '0',
            'index_min' => '11.5', 'index_max' => '22.0', 'joker_h' => '1', 'joker_d' => '1',
            'mixte' => '1', 'forfait_score' => '15',
        ]);
        self::assertSame(['S', 'S', 'D'], $s->structure);
        self::assertSame(15, $s->forfaitScore);
    }

    public function testResultatInconnuRefuse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Serie::h1()->pointsPartie('X');
    }
}
