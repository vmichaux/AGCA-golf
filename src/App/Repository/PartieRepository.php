<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class PartieRepository extends Repository
{
    public const COLONNES = ['numero', 'type', 'rec_joueur1_id', 'rec_index1', 'rec_sexe1', 'rec_joueur2_id', 'rec_index2', 'rec_sexe2',
        'inv_joueur1_id', 'inv_index1', 'inv_sexe1', 'inv_joueur2_id', 'inv_index2', 'inv_sexe2',
        'resultat', 'score_trous', 'score_restants', 'score_as', 'pts_pour', 'pts_contre'];

    public function parRencontre(int $rencontreId): array
    {
        return $this->db->all('SELECT p.*, r1.nom AS rec_nom1, r2.nom AS rec_nom2, i1.nom AS inv_nom1, i2.nom AS inv_nom2
            FROM agca_partie p
            LEFT JOIN agca_joueur r1 ON r1.id = p.rec_joueur1_id LEFT JOIN agca_joueur r2 ON r2.id = p.rec_joueur2_id
            LEFT JOIN agca_joueur i1 ON i1.id = p.inv_joueur1_id LEFT JOIN agca_joueur i2 ON i2.id = p.inv_joueur2_id
            WHERE p.rencontre_id = ? ORDER BY p.numero', [$rencontreId]);
    }

    /** @param list<array<string,mixed>> $parties */
    public function remplacer(int $rencontreId, array $parties): void
    {
        $this->db->transaction(function () use ($rencontreId, $parties) {
            $this->db->exec('DELETE FROM agca_partie WHERE rencontre_id = ?', [$rencontreId]);
            $cols = implode(', ', self::COLONNES);
            $marks = implode(', ', array_fill(0, count(self::COLONNES), '?'));
            foreach ($parties as $p) {
                $vals = array_map(fn($c) => $p[$c] ?? null, self::COLONNES);
                $this->db->exec("INSERT INTO agca_partie (rencontre_id, $cols) VALUES (?, $marks)", [$rencontreId, ...$vals]);
            }
        });
    }
}
