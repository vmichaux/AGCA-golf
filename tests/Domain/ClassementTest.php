<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use Agca\Domain\Classement;
use PHPUnit\Framework\TestCase;

final class ClassementTest extends TestCase
{
    private const EQUIPES = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D'];

    /** @return array<string,mixed> */
    private static function r(int $rec, int $inv, int $pour, int $contre, string $statut = 'enregistree', ?int $forfaitaire = null): array
    {
        if ($statut === 'forfait') {
            $benefRec = $forfaitaire === $inv;
            return ['recevant_id' => $rec, 'invite_id' => $inv, 'statut' => 'forfait',
                'total_pour' => $benefRec ? 15 : 0, 'total_contre' => $benefRec ? 0 : 15,
                'pts_rencontre_pour' => $benefRec ? 3.0 : 0.0, 'pts_rencontre_contre' => $benefRec ? 0.0 : 3.0,
                'bonus_invite' => $benefRec ? 0.0 : 1.0, 'forfaitaire_id' => $forfaitaire];
        }
        $pp = $pour > $contre ? 3.0 : ($pour < $contre ? 1.0 : 2.0);
        $pc = $pour > $contre ? 1.0 : ($pour < $contre ? 3.0 : 2.0);
        $bonus = $pour < $contre ? 1.0 : ($pour === $contre ? 0.5 : 0.0);
        return ['recevant_id' => $rec, 'invite_id' => $inv, 'statut' => $statut,
            'total_pour' => $pour, 'total_contre' => $contre,
            'pts_rencontre_pour' => $pp, 'pts_rencontre_contre' => $pc, 'bonus_invite' => $bonus, 'forfaitaire_id' => null];
    }

    public function testColonnesEtOrdreParPoints(): void
    {
        $lignes = Classement::calculer(self::EQUIPES, [
            self::r(1, 2, 20, 10),   // A bat B à domicile : A 3, B 1
            self::r(3, 4, 12, 18),   // D bat C à l'extérieur : D 3+1, C 1
            self::r(1, 3, 15, 15),   // nul : A 2, C 2+0,5
            self::r(2, 4, 0, 0, 'a_jouer'),
        ]);
        self::assertSame([1, 4, 3, 2], array_column($lignes, 'equipe_id'));
        self::assertSame([1, 5.0, 0.0, 2, 1, 1], [$lignes[0]['rang'], $lignes[0]['points'], $lignes[0]['bonus'], $lignes[0]['joues'], $lignes[0]['gagnes'], $lignes[0]['nuls']]);
        $d = $lignes[1];
        self::assertSame(['rang' => 2, 'equipe_id' => 4, 'nom' => 'D', 'points' => 4.0, 'bonus' => 1.0,
            'joues' => 1, 'gagnes' => 1, 'nuls' => 0, 'perdus' => 0, 'forfaits' => 0,
            'pour' => 18, 'contre' => 12, 'diff' => 6], $d);
        $c = $lignes[2];
        self::assertSame([3.5, 0.5, 1, 0, 1, 2], [$c['points'], $c['bonus'], $c['nuls'], $c['gagnes'], $c['perdus'], $c['joues']]);
        self::assertSame(4, $lignes[3]['rang']);
    }

    public function testDepartageConfrontationDirecte(): void
    {
        // A et B à 4 points chacun. A a battu B (3) et perdu le retour à l'extérieur (1) => 4 ; B : 1 + 3 = 4.
        // Ajout : B a fait nul à l'extérieur contre C (2,5) ; A a battu C à l'extérieur (4). Egalité brisée par le diff ?
        // Ici on force l'égalité de points et on teste la confrontation directe avec bonus : B gagne le retour chez elle sans bonus.
        $lignes = Classement::calculer([1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D'], [
            self::r(1, 2, 20, 10),   // A 3 / B 1
            self::r(2, 1, 16, 14),   // B 3 / A 1  => confrontation directe A 4, B 4
            self::r(1, 3, 10, 20),   // C gagne dehors : A 1, C 4
            self::r(2, 4, 10, 20),   // D gagne dehors : B 1, D 4
            self::r(3, 1, 10, 20),   // A gagne dehors : A 4  => A total 9
            self::r(4, 2, 12, 18),   // B gagne dehors : B 4  => B total 9
        ]);
        // A : 3+1+1+4 = 9, diff = +10 -2 -10 +10 = +8 ; B : 1+3+1+4 = 9, diff = -10 +2 -10 +6 = -12
        // confrontation directe : A 3+1 = 4, B 1+3 = 4 => égalité, puis diff => A devant
        self::assertSame([1, 2], array_slice(array_column($lignes, 'equipe_id'), 0, 2));
        self::assertSame(1, $lignes[0]['rang']);
        self::assertSame(2, $lignes[1]['rang']);
    }

    public function testConfrontationDirecteAvecBonusPrimeSurLeDiff(): void
    {
        // A et B finissent à 14,5 points. A a le meilleur diff (+18 contre -16) mais B a pris
        // 6 points dans leurs deux confrontations (victoire à l'extérieur 3+1, nul chez elle 2) contre 3,5 pour A.
        $lignes = Classement::calculer([1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D'], [
            self::r(1, 2, 14, 16),  // B gagne chez A : A 1, B 4
            self::r(2, 1, 15, 15),  // nul chez B : B 2, A 2,5
            self::r(1, 3, 20, 10),  // A 3
            self::r(3, 1, 10, 20),  // A 4
            self::r(1, 4, 20, 10),  // A 3
            self::r(4, 1, 20, 10),  // D 3, A 1
            self::r(2, 3, 16, 14),  // B 3
            self::r(3, 2, 15, 15),  // C 2, B 2,5
            self::r(2, 4, 15, 15),  // B 2, D 2,5
            self::r(4, 2, 25, 5),   // D 3, B 1
        ]);
        self::assertSame([14.5, 14.5], [$lignes[0]['points'], $lignes[1]['points']]);
        self::assertSame([2, 1, 4, 3], array_column($lignes, 'equipe_id'));
        self::assertSame([1, 2, 3, 4], array_column($lignes, 'rang'));
        self::assertSame(18, $lignes[1]['diff']);
        self::assertSame(-16, $lignes[0]['diff']);
    }

    public function testTroisExAequoMiniChampionnatPuisDiff(): void
    {
        // A, B, C chacun 1 victoire à domicile, 1 défaite à domicile dans le triangle : 4 pts chacun.
        $lignes = Classement::calculer([1 => 'A', 2 => 'B', 3 => 'C'], [
            self::r(1, 2, 20, 10),  // A 3, B 1
            self::r(2, 3, 20, 10),  // B 3, C 1
            self::r(3, 1, 25, 5),   // C 3, A 1   => tous 4 pts ; directs égaux ; diff : A -10, B 0, C +10
        ]);
        self::assertSame([3, 2, 1], array_column($lignes, 'equipe_id'));
        self::assertSame([1, 2, 3], array_column($lignes, 'rang'));
    }

    public function testExAequoCompletsPartagentLeRang(): void
    {
        $lignes = Classement::calculer([1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D'], [
            self::r(1, 2, 15, 15),  // A 2, B 2,5
            self::r(3, 4, 15, 15),  // C 2, D 2,5
        ]);
        self::assertSame([1, 1, 3, 3], array_column($lignes, 'rang'));
    }

    public function testForfaitCompteDansJouesEtForfaits(): void
    {
        $lignes = Classement::calculer([1 => 'A', 2 => 'B'], [
            self::r(1, 2, 0, 0, 'forfait', 1),  // A forfait chez elle : B 3 + 1
        ]);
        self::assertSame(2, $lignes[0]['equipe_id']);
        self::assertSame([4.0, 1, 1, 15, 0], [$lignes[0]['points'], $lignes[0]['joues'], $lignes[0]['gagnes'], $lignes[0]['pour'], $lignes[0]['forfaits']]);
        self::assertSame([0.0, 1, 1, 1, 0, 15], [$lignes[1]['points'], $lignes[1]['joues'], $lignes[1]['forfaits'], $lignes[1]['perdus'], $lignes[1]['pour'], $lignes[1]['contre']]);
    }

    public function testEquipeSansMatchApparaitAZero(): void
    {
        $lignes = Classement::calculer([1 => 'A', 2 => 'B'], []);
        self::assertCount(2, $lignes);
        self::assertSame([1, 1], array_column($lignes, 'rang'));
        self::assertSame(0.0, $lignes[0]['points']);
    }
}
