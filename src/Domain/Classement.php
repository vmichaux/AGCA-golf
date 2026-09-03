<?php
declare(strict_types=1);
namespace Agca\Domain;

final class Classement
{
    /**
     * @param array<int,string> $equipes id => nom
     * @param list<array<string,mixed>> $rencontres voir interface de la tâche
     * @return list<array<string,mixed>>
     */
    public static function calculer(array $equipes, array $rencontres): array
    {
        $lignes = [];
        foreach ($equipes as $id => $nom) {
            $lignes[$id] = ['rang' => 0, 'equipe_id' => (int) $id, 'nom' => $nom, 'points' => 0.0, 'bonus' => 0.0,
                'joues' => 0, 'gagnes' => 0, 'nuls' => 0, 'perdus' => 0, 'forfaits' => 0, 'pour' => 0, 'contre' => 0, 'diff' => 0];
        }
        $jouees = array_values(array_filter($rencontres, fn($r) => $r['statut'] !== 'a_jouer'));
        foreach ($jouees as $r) {
            $rec = (int) $r['recevant_id']; $inv = (int) $r['invite_id'];
            if (!isset($lignes[$rec]) || !isset($lignes[$inv])) { continue; }
            $pour = (int) $r['total_pour']; $contre = (int) $r['total_contre'];
            $bonus = (float) $r['bonus_invite'];
            self::ajouter($lignes[$rec], (float) $r['pts_rencontre_pour'], 0.0, $pour, $contre);
            self::ajouter($lignes[$inv], (float) $r['pts_rencontre_contre'], $bonus, $contre, $pour);
            if ($r['statut'] === 'forfait' && $r['forfaitaire_id'] !== null) {
                $lignes[(int) $r['forfaitaire_id']]['forfaits']++;
            }
        }
        foreach ($lignes as &$l) { $l['diff'] = $l['pour'] - $l['contre']; }
        unset($l);

        $ordre = self::ordonner(array_values($lignes), $jouees, 0);
        $rang = 0;
        foreach ($ordre as $i => [$ligne, $nouveauGroupe]) {
            if ($nouveauGroupe) { $rang = $i + 1; }
            $ligne['rang'] = $rang;
            $ordre[$i] = $ligne;
        }
        return $ordre;
    }

    private static function ajouter(array &$l, float $pts, float $bonus, int $pour, int $contre): void
    {
        $l['joues']++; $l['points'] += $pts + $bonus; $l['bonus'] += $bonus;
        $l['pour'] += $pour; $l['contre'] += $contre;
        if ($pour > $contre) { $l['gagnes']++; } elseif ($pour < $contre) { $l['perdus']++; } else { $l['nuls']++; }
    }

    /**
     * Tri récursif : niveau 0 = points, 1 = confrontation directe, 2 = diff, 3 = ex æquo.
     * @return list<array{0:array<string,mixed>,1:bool}> ligne + « ouvre un nouveau groupe de rang »
     */
    private static function ordonner(array $lignes, array $rencontres, int $niveau): array
    {
        if (count($lignes) <= 1 || $niveau > 2) {
            return array_map(fn($l, $i) => [$l, $i === 0], $lignes, array_keys($lignes));
        }
        $ids = array_column($lignes, 'equipe_id');
        $cle = match ($niveau) {
            0 => fn(array $l) => $l['points'],
            1 => fn(array $l) => self::pointsDirects($l['equipe_id'], $ids, $rencontres),
            2 => fn(array $l) => $l['diff'],
        };
        $groupes = [];
        foreach ($lignes as $l) { $groupes[(string) $cle($l)][] = $l; }
        krsort($groupes, SORT_NUMERIC);
        $out = [];
        foreach ($groupes as $groupe) {
            if (count($groupe) === 1) { $out[] = [$groupe[0], true]; continue; }
            $sous = self::ordonner($groupe, $rencontres, $niveau + 1);
            foreach ($sous as $i => [$l, $nouveau]) { $out[] = [$l, $i === 0 ? true : $nouveau]; }
        }
        return $out;
    }

    /** Points de rencontre + bonus obtenus contre les autres équipes du groupe. */
    private static function pointsDirects(int $id, array $ids, array $rencontres): float
    {
        $total = 0.0;
        foreach ($rencontres as $r) {
            $rec = (int) $r['recevant_id']; $inv = (int) $r['invite_id'];
            if (!in_array($rec, $ids, true) || !in_array($inv, $ids, true)) { continue; }
            if ($rec === $id) { $total += (float) $r['pts_rencontre_pour']; }
            elseif ($inv === $id) { $total += (float) $r['pts_rencontre_contre'] + (float) $r['bonus_invite']; }
        }
        return $total;
    }
}
