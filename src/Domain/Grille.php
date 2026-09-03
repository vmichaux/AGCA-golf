<?php
declare(strict_types=1);
namespace Agca\Domain;

/** Grille des rencontres à partir des positions 1..n fixées par l'admin. */
final class Grille
{
    /** journée => [[recevant, invité], ...] */
    private const POULE4_ALLER = [
        1 => [[1, 2], [3, 4]],
        2 => [[2, 3], [4, 1]],
        3 => [[3, 1], [4, 2]],
    ];

    /** Grille du site actuel : une équipe exempte par journée. */
    private const POULE5_ALLER = [
        1 => [[1, 2], [4, 3]],
        2 => [[3, 1], [2, 5]],
        3 => [[1, 4], [5, 3]],
        4 => [[5, 1], [2, 4]],
        5 => [[3, 2], [4, 5]],
    ];

    private const POULE5_RETOUR = [
        1 => [[2, 1], [3, 4]],
        2 => [[1, 5], [2, 3]],
        3 => [[5, 4], [1, 3]],
        4 => [[5, 2], [4, 1]],
        5 => [[3, 5], [4, 2]],
    ];

    public static function nbJournees(int $nbEquipes): int
    {
        return match ($nbEquipes) { 4 => 3, 5 => 5,
            default => throw new \InvalidArgumentException("Poule à $nbEquipes équipes non prévue") };
    }

    /** @return list<array{journee:int, phase:string, recevant:int, invite:int}> */
    public static function generer(int $nbEquipes): array
    {
        [$aller, $retour] = match ($nbEquipes) {
            4 => [self::POULE4_ALLER, self::inverser(self::POULE4_ALLER)],
            5 => [self::POULE5_ALLER, self::POULE5_RETOUR],
            default => throw new \InvalidArgumentException("Poule à $nbEquipes équipes non prévue"),
        };
        $out = [];
        foreach (['aller' => $aller, 'retour' => $retour] as $phase => $grille) {
            foreach ($grille as $journee => $matchs) {
                foreach ($matchs as [$rec, $inv]) {
                    $out[] = ['journee' => $journee, 'phase' => $phase, 'recevant' => $rec, 'invite' => $inv];
                }
            }
        }
        return $out;
    }

    /** @param array<int, list<array{0:int,1:int}>> $grille */
    private static function inverser(array $grille): array
    {
        $out = [];
        foreach ($grille as $j => $matchs) {
            $out[$j] = array_map(fn(array $m) => [$m[1], $m[0]], $matchs);
        }
        return $out;
    }
}
