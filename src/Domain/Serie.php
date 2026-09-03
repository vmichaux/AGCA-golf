<?php
declare(strict_types=1);
namespace Agca\Domain;

final class Serie
{
    /** @param list<'S'|'D'> $structure */
    public function __construct(
        public readonly string $code,
        public readonly string $libelle,
        public readonly array $structure,
        public readonly int $ptsGagne,
        public readonly int $ptsNul,
        public readonly int $ptsPerdu,
        public readonly ?float $indexMin,
        public readonly ?float $indexMax,
        public readonly int $jokerH,
        public readonly int $jokerD,
        public readonly bool $mixte,
        public readonly int $forfaitScore = 15,
    ) {
        foreach ($structure as $t) {
            if ($t !== 'S' && $t !== 'D') {
                throw new \InvalidArgumentException("Type de partie inconnu : $t");
            }
        }
    }

    public static function mixte(): self
    {
        return new self('M', 'Mixte 2e série', array_merge(...array_fill(0, 5, ['S', 'S', 'D'])),
            2, 1, 0, 11.5, 22.0, 1, 1, true);
    }

    public static function h1(): self
    {
        return new self('H1', 'Homme 1re série', ['D', 'S', 'S', 'S', 'S'],
            3, 2, 1, null, null, 0, 0, false);
    }

    /** @param array<string, mixed> $r ligne de agca_serie */
    public static function depuisLigne(array $r): self
    {
        return new self(
            (string) $r['code'], (string) $r['libelle'], str_split((string) $r['structure']),
            (int) $r['pts_gagne'], (int) $r['pts_nul'], (int) $r['pts_perdu'],
            $r['index_min'] === null ? null : (float) $r['index_min'],
            $r['index_max'] === null ? null : (float) $r['index_max'],
            (int) $r['joker_h'], (int) $r['joker_d'], (bool) (int) $r['mixte'],
            (int) ($r['forfait_score'] ?? 15),
        );
    }

    public function nbParties(): int { return count($this->structure); }

    public function structureTexte(): string { return implode('', $this->structure); }

    /** @return array{0:int,1:int} [points recevant, points invité] */
    public function pointsPartie(?string $resultat): array
    {
        return match ($resultat) {
            null => [0, 0],
            'G' => [$this->ptsGagne, $this->ptsPerdu],
            'N' => [$this->ptsNul, $this->ptsNul],
            'P' => [$this->ptsPerdu, $this->ptsGagne],
            default => throw new \InvalidArgumentException("Résultat inconnu : $resultat"),
        };
    }
}
