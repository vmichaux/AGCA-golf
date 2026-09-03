<?php
declare(strict_types=1);
namespace Agca\Domain;

final class CalculRencontre
{
    public const RENCONTRE_GAGNEE = 3.0;
    public const RENCONTRE_NULLE = 2.0;
    public const RENCONTRE_PERDUE = 1.0;
    public const BONUS_VICTOIRE_EXTERIEUR = 1.0;
    public const BONUS_NUL_EXTERIEUR = 0.5;

    /**
     * @param list<'G'|'N'|'P'|null> $resultats un résultat par partie, vu du recevant (ignoré si forfait)
     * @param 'recevant'|'invite'|null $forfait équipe forfaitaire
     */
    public static function calculer(Serie $serie, array $resultats, ?string $forfait = null): ResultatRencontre
    {
        if ($forfait !== null) {
            if (!in_array($forfait, ['recevant', 'invite'], true)) {
                throw new \InvalidArgumentException("Forfait inconnu : $forfait");
            }
            $s = $serie->forfaitScore;
            return $forfait === 'recevant'
                ? new ResultatRencontre(0, $s, 0.0, self::RENCONTRE_GAGNEE, self::BONUS_VICTOIRE_EXTERIEUR, [])
                : new ResultatRencontre($s, 0, self::RENCONTRE_GAGNEE, 0.0, 0.0, []);
        }
        if (count($resultats) !== $serie->nbParties()) {
            throw new \InvalidArgumentException(sprintf('%d résultats attendus, %d reçus', $serie->nbParties(), count($resultats)));
        }
        $pts = []; $pour = 0; $contre = 0;
        foreach ($resultats as $r) {
            [$p, $c] = $serie->pointsPartie($r);
            $pts[] = [$p, $c]; $pour += $p; $contre += $c;
        }
        if ($pour > $contre) {
            return new ResultatRencontre($pour, $contre, self::RENCONTRE_GAGNEE, self::RENCONTRE_PERDUE, 0.0, $pts);
        }
        if ($pour < $contre) {
            return new ResultatRencontre($pour, $contre, self::RENCONTRE_PERDUE, self::RENCONTRE_GAGNEE, self::BONUS_VICTOIRE_EXTERIEUR, $pts);
        }
        return new ResultatRencontre($pour, $contre, self::RENCONTRE_NULLE, self::RENCONTRE_NULLE, self::BONUS_NUL_EXTERIEUR, $pts);
    }
}
