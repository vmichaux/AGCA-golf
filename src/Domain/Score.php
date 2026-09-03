<?php
declare(strict_types=1);
namespace Agca\Domain;

/** Score structuré d'une partie en match play : « 3&2 », « 1 UP », « AS ». */
final class Score
{
    public function __construct(
        public readonly int $trous,
        public readonly int $restants,
        public readonly bool $as,
    ) {
        if ($as && ($trous !== 0 || $restants !== 0)) {
            throw new \InvalidArgumentException('Un score AS n\'a ni trous d\'avance ni trous restants');
        }
        if (!$as && ($trous < 1 || $trous > 10 || $restants < 0 || $restants > 9 || ($restants > 0 && $restants >= $trous))) {
            throw new \InvalidArgumentException('Score hors limites : les trous restants doivent être inférieurs aux trous d\'avance');
        }
    }

    public static function depuisTexte(?string $texte): ?self
    {
        $t = strtoupper(trim((string) $texte));
        if ($t === '') { return null; }
        if (in_array($t, ['AS', 'A/S', 'A.S.', 'SQUARE', 'ALL SQUARE', 'NUL'], true)) {
            return new self(0, 0, true);
        }
        if (preg_match('/^(\d{1,2})\s*(?:&|ET|\/|-)\s*(\d)$/', $t, $m)) {
            return new self((int) $m[1], (int) $m[2], false);
        }
        if (preg_match('/^(\d{1,2})\s*UP$/', $t, $m)) {
            return new self((int) $m[1], 0, false);
        }
        throw new \InvalidArgumentException("Score illisible : $texte");
    }

    public function texte(): string
    {
        if ($this->as) { return 'AS'; }
        return $this->restants === 0 ? "{$this->trous} UP" : "{$this->trous}&{$this->restants}";
    }

    /** Un score AS implique un nul ; un score avec avance implique gagné ou perdu. */
    public function coherentAvec(?string $resultat): bool
    {
        if ($resultat === null) { return true; }
        return $this->as ? $resultat === 'N' : $resultat !== 'N';
    }
}
