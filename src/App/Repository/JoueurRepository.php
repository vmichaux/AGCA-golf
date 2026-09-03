<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class JoueurRepository extends Repository
{
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_joueur WHERE id = ?', [$id]); }

    public function parGolf(int $golfId): array
    {
        return $this->db->all('SELECT * FROM agca_joueur WHERE golf_id = ? AND fusionne_dans IS NULL ORDER BY nom, prenom', [$golfId]);
    }

    public function chercher(int $golfId, string $q, int $limite = 10): array
    {
        $q = trim($q);
        return $this->db->all('SELECT id, nom, prenom, sexe, dernier_index FROM agca_joueur WHERE golf_id = ? AND fusionne_dans IS NULL AND nom LIKE ? ORDER BY nom LIMIT ' . (int) $limite,
            [$golfId, $q === '' ? '%' : $q . '%']);
    }

    public function trouverOuCreer(int $golfId, string $nom, ?string $sexe, ?float $index, ?int $parUtilisateur): int
    {
        $nom = mb_strtoupper(trim(preg_replace('/\s+/', ' ', $nom) ?? $nom));
        $j = $this->db->one('SELECT * FROM agca_joueur WHERE golf_id = ? AND UPPER(nom) = ? AND fusionne_dans IS NULL', [$golfId, $nom]);
        if ($j === null) {
            return $this->db->insert('INSERT INTO agca_joueur (golf_id, nom, sexe, dernier_index, cree_par) VALUES (?, ?, ?, ?, ?)', [$golfId, $nom, $sexe, $index, $parUtilisateur]);
        }
        $id = (int) $j['id'];
        if ($index !== null) { $this->db->exec('UPDATE agca_joueur SET dernier_index = ? WHERE id = ?', [$index, $id]); }
        if ($sexe !== null && $j['sexe'] === null) { $this->db->exec('UPDATE agca_joueur SET sexe = ? WHERE id = ?', [$sexe, $id]); }
        return $id;
    }

    public function modifier(int $id, string $nom, ?string $prenom, ?string $sexe): void
    {
        $this->db->exec('UPDATE agca_joueur SET nom = ?, prenom = ?, sexe = ? WHERE id = ?', [mb_strtoupper(trim($nom)), $prenom, $sexe, $id]);
    }

    public function fusionner(int $sourceId, int $cibleId): int
    {
        return (int) $this->db->transaction(function () use ($sourceId, $cibleId) {
            $n = 0;
            foreach (['rec_joueur1_id', 'rec_joueur2_id', 'inv_joueur1_id', 'inv_joueur2_id'] as $col) {
                $n += $this->db->exec("UPDATE agca_partie SET $col = ? WHERE $col = ?", [$cibleId, $sourceId]);
            }
            $this->db->exec('UPDATE agca_joueur SET fusionne_dans = ? WHERE id = ?', [$cibleId, $sourceId]);
            return $n;
        });
    }
}
