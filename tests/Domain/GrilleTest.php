<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use Agca\Domain\Grille;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GrilleTest extends TestCase
{
    public function testPouleA4Aller(): void
    {
        $aller = array_values(array_filter(Grille::generer(4), fn($r) => $r['phase'] === 'aller'));
        self::assertSame([
            ['journee' => 1, 'phase' => 'aller', 'recevant' => 1, 'invite' => 2],
            ['journee' => 1, 'phase' => 'aller', 'recevant' => 3, 'invite' => 4],
            ['journee' => 2, 'phase' => 'aller', 'recevant' => 2, 'invite' => 3],
            ['journee' => 2, 'phase' => 'aller', 'recevant' => 4, 'invite' => 1],
            ['journee' => 3, 'phase' => 'aller', 'recevant' => 3, 'invite' => 1],
            ['journee' => 3, 'phase' => 'aller', 'recevant' => 4, 'invite' => 2],
        ], $aller);
    }

    public function testPouleA4RetourDomicilesInverses(): void
    {
        $retour = array_values(array_filter(Grille::generer(4), fn($r) => $r['phase'] === 'retour'));
        self::assertSame(['journee' => 1, 'phase' => 'retour', 'recevant' => 2, 'invite' => 1], $retour[0]);
        self::assertSame(['journee' => 3, 'phase' => 'retour', 'recevant' => 2, 'invite' => 4], $retour[5]);
        self::assertCount(12, Grille::generer(4));
    }

    public function testPouleA5GrilleDuSite(): void
    {
        $g = Grille::generer(5);
        self::assertCount(20, $g);
        $j2aller = array_values(array_filter($g, fn($r) => $r['phase'] === 'aller' && $r['journee'] === 2));
        self::assertSame([[3, 1], [2, 5]], array_map(fn($r) => [$r['recevant'], $r['invite']], $j2aller));
        $j2retour = array_values(array_filter($g, fn($r) => $r['phase'] === 'retour' && $r['journee'] === 2));
        self::assertSame([[1, 5], [2, 3]], array_map(fn($r) => [$r['recevant'], $r['invite']], $j2retour));
    }

    #[DataProvider('tailles')]
    public function testChaquePaireUneFoisAllerUneFoisRetour(int $n): void
    {
        $g = Grille::generer($n);
        $aller = []; $retour = [];
        foreach ($g as $r) {
            $cle = min($r['recevant'], $r['invite']) . '-' . max($r['recevant'], $r['invite']);
            if ($r['phase'] === 'aller') { $aller[$cle] = [$r['recevant'], $r['invite']]; }
            else { $retour[$cle] = [$r['recevant'], $r['invite']]; }
        }
        self::assertCount($n * ($n - 1) / 2, $aller);
        $clesAller = array_keys($aller); sort($clesAller);
        $clesRetour = array_keys($retour); sort($clesRetour);
        self::assertSame($clesAller, $clesRetour);
        foreach ($aller as $cle => [$rec, $inv]) {
            self::assertSame([$inv, $rec], $retour[$cle], "Retour non inversé pour $cle");
        }
    }

    public function testPouleA5UneEquipeExempteParJournee(): void
    {
        foreach (['aller', 'retour'] as $phase) {
            for ($j = 1; $j <= 5; $j++) {
                $joue = [];
                foreach (Grille::generer(5) as $r) {
                    if ($r['phase'] === $phase && $r['journee'] === $j) { $joue[] = $r['recevant']; $joue[] = $r['invite']; }
                }
                sort($joue);
                self::assertCount(4, array_unique($joue), "$phase J$j");
            }
        }
    }

    public function testTailleInconnueRefusee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Grille::generer(6);
    }

    public static function tailles(): array { return [[4], [5]]; }
}
