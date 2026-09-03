<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use Agca\Domain\Score;
use PHPUnit\Framework\TestCase;

final class ScoreTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('lectures')]
    public function testLecture(string $texte, int $trous, int $restants, bool $as, string $affiche): void
    {
        $s = Score::depuisTexte($texte);
        self::assertNotNull($s);
        self::assertSame([$trous, $restants, $as], [$s->trous, $s->restants, $s->as]);
        self::assertSame($affiche, $s->texte());
    }

    public static function lectures(): array
    {
        return [
            ['3&2', 3, 2, false, '3&2'],
            ['3ET2', 3, 2, false, '3&2'],
            ['3 et 2', 3, 2, false, '3&2'],
            ['3/2', 3, 2, false, '3&2'],
            ['1UP', 1, 0, false, '1 UP'],
            ['2 up', 2, 0, false, '2 UP'],
            ['AS', 0, 0, true, 'AS'],
            ['square', 0, 0, true, 'AS'],
            ['A/S', 0, 0, true, 'AS'],
        ];
    }

    public function testVideDonneNull(): void
    {
        self::assertNull(Score::depuisTexte(''));
        self::assertNull(Score::depuisTexte(null));
        self::assertNull(Score::depuisTexte('  '));
    }

    public function testIllisibleRefuse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Score::depuisTexte('40et');
    }

    public function testCoherence(): void
    {
        self::assertTrue(Score::depuisTexte('AS')->coherentAvec('N'));
        self::assertFalse(Score::depuisTexte('AS')->coherentAvec('G'));
        self::assertTrue(Score::depuisTexte('3&2')->coherentAvec('G'));
        self::assertTrue(Score::depuisTexte('3&2')->coherentAvec('P'));
        self::assertFalse(Score::depuisTexte('1UP')->coherentAvec('N'));
        self::assertTrue(Score::depuisTexte('1UP')->coherentAvec(null));
    }

    public function testTexteDepuisColonnes(): void
    {
        self::assertSame('3&2', Score::texteDepuisColonnes('3', '2', '0'));
        self::assertSame('AS', Score::texteDepuisColonnes(null, null, '1'));
        self::assertSame('', Score::texteDepuisColonnes(null, null, 0));
        self::assertSame('1 UP', Score::texteDepuisColonnes('1', '0', 0));
    }
}
