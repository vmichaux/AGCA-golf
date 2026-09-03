<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use Agca\Domain\CalculRencontre;
use Agca\Domain\Serie;
use PHPUnit\Framework\TestCase;

final class CalculRencontreTest extends TestCase
{
    public function testMixteVictoireRecevant(): void
    {
        // 10 parties gagnées, 2 nulles, 3 perdues => 22-8
        $res = array_merge(array_fill(0, 10, 'G'), ['N', 'N'], ['P', 'P', 'P']);
        $r = CalculRencontre::calculer(Serie::mixte(), $res);
        self::assertSame(22, $r->totalPour);
        self::assertSame(8, $r->totalContre);
        self::assertSame(3.0, $r->ptsPour);
        self::assertSame(1.0, $r->ptsContre);
        self::assertSame(0.0, $r->bonusInvite);
        self::assertSame([2, 0], $r->ptsParties[0]);
        self::assertSame([1, 1], $r->ptsParties[10]);
        self::assertCount(15, $r->ptsParties);
    }

    public function testMixteVictoireInviteDonneBonus1(): void
    {
        $res = array_merge(array_fill(0, 5, 'G'), array_fill(0, 10, 'P'));
        $r = CalculRencontre::calculer(Serie::mixte(), $res);
        self::assertSame([10, 20], [$r->totalPour, $r->totalContre]);
        self::assertSame([1.0, 3.0, 1.0], [$r->ptsPour, $r->ptsContre, $r->bonusInvite]);
    }

    public function testMixteNulDonneBonusDemi(): void
    {
        $res = array_merge(array_fill(0, 7, 'G'), ['N'], array_fill(0, 7, 'P'));
        $r = CalculRencontre::calculer(Serie::mixte(), $res);
        self::assertSame([15, 15], [$r->totalPour, $r->totalContre]);
        self::assertSame([2.0, 2.0, 0.5], [$r->ptsPour, $r->ptsContre, $r->bonusInvite]);
    }

    public function testH1Points321(): void
    {
        $r = CalculRencontre::calculer(Serie::h1(), ['G', 'G', 'N', 'P', 'G']);
        self::assertSame([12, 8], [$r->totalPour, $r->totalContre]);
        self::assertSame(3.0, $r->ptsPour);
    }

    public function testFeuilleIncompleteComptePourZero(): void
    {
        $r = CalculRencontre::calculer(Serie::h1(), ['G', null, null, null, null]);
        self::assertSame([3, 1], [$r->totalPour, $r->totalContre]);
        self::assertSame([0, 0], $r->ptsParties[1]);
    }

    public function testForfaitDuRecevant(): void
    {
        $r = CalculRencontre::calculer(Serie::mixte(), [], 'recevant');
        self::assertSame([0, 15], [$r->totalPour, $r->totalContre]);
        self::assertSame([0.0, 3.0, 1.0], [$r->ptsPour, $r->ptsContre, $r->bonusInvite]);
        self::assertSame([], $r->ptsParties);
    }

    public function testForfaitDeLInvite(): void
    {
        $r = CalculRencontre::calculer(Serie::h1(), [], 'invite');
        self::assertSame([15, 0], [$r->totalPour, $r->totalContre]);
        self::assertSame([3.0, 0.0, 0.0], [$r->ptsPour, $r->ptsContre, $r->bonusInvite]);
    }

    public function testNombreDePartiesIncorrectRefuse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CalculRencontre::calculer(Serie::h1(), ['G', 'G']);
    }
}
