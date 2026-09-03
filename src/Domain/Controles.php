<?php
declare(strict_types=1);
namespace Agca\Domain;

final class Controles
{
    public const LIBELLES = [
        'INDEX_HORS_BORNES' => 'Index hors des bornes de la série',
        'JOKER_H_MULTIPLE' => 'Plus d\'un joker homme sous l\'index minimum',
        'JOKER_D_MULTIPLE' => 'Plus d\'une joker dame sous l\'index minimum',
        'FEUILLE_INCOMPLETE' => 'Feuille incomplète (joueur, index ou résultat manquant)',
        'SCORE_INCOHERENT' => 'Score incohérent avec le résultat',
    ];

    /**
     * @param list<array{type:string, rec:list<array{nom:?string,index:?float,sexe:?string}>, inv:list<array{nom:?string,index:?float,sexe:?string}>, resultat:?string, score:?Score}> $parties
     * @return list<string>
     */
    public static function verifier(Serie $serie, array $parties): array
    {
        $codes = [];
        $joueurs = ['rec' => [], 'inv' => []]; // nom => [index, sexe]
        foreach ($parties as $p) {
            if ($p['resultat'] === null) { $codes[] = 'FEUILLE_INCOMPLETE'; }
            if ($p['score'] !== null && !$p['score']->coherentAvec($p['resultat'])) { $codes[] = 'SCORE_INCOHERENT'; }
            foreach (['rec', 'inv'] as $camp) {
                foreach ($p[$camp] as $j) {
                    $nom = trim((string) ($j['nom'] ?? ''));
                    if ($nom === '' || $j['index'] === null) { $codes[] = 'FEUILLE_INCOMPLETE'; continue; }
                    $joueurs[$camp][mb_strtoupper($nom)] = [(float) $j['index'], $j['sexe'] ?? null];
                }
            }
        }
        if ($serie->indexMin !== null || $serie->indexMax !== null) {
            foreach ($joueurs as $camp => $liste) {
                $jokersH = 0; $jokersD = 0;
                foreach ($liste as [$index, $sexe]) {
                    if ($serie->indexMax !== null && $index > $serie->indexMax) { $codes[] = 'INDEX_HORS_BORNES'; continue; }
                    if ($serie->indexMin !== null && $index < $serie->indexMin) {
                        if ($sexe === 'H') { $jokersH++; }
                        elseif ($sexe === 'D') { $jokersD++; }
                        else { $codes[] = 'INDEX_HORS_BORNES'; }
                    }
                }
                if ($jokersH > $serie->jokerH) { $codes[] = $serie->jokerH === 0 ? 'INDEX_HORS_BORNES' : 'JOKER_H_MULTIPLE'; }
                if ($jokersD > $serie->jokerD) { $codes[] = $serie->jokerD === 0 ? 'INDEX_HORS_BORNES' : 'JOKER_D_MULTIPLE'; }
            }
        }
        return array_values(array_unique($codes));
    }
}
