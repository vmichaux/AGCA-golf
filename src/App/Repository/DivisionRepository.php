<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class DivisionRepository extends Repository
{
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_division WHERE id = ?', [$id]); }

    public function parSaisonEtSerie(int $saisonId, int $serieId): array
    {
        return $this->db->all('SELECT * FROM agca_division WHERE saison_id = ? AND serie_id = ? ORDER BY ordre, id', [$saisonId, $serieId]);
    }

    public function creer(int $saisonId, int $serieId, string $libelle, int $ordre): int
    {
        return $this->db->insert('INSERT INTO agca_division (saison_id, serie_id, libelle, ordre) VALUES (?, ?, ?, ?)', [$saisonId, $serieId, $libelle, $ordre]);
    }

    public function ajouterEquipe(int $divisionId, int $equipeId, int $position): void
    {
        $this->db->exec('INSERT INTO agca_division_equipe (division_id, equipe_id, position) VALUES (?, ?, ?)', [$divisionId, $equipeId, $position]);
    }

    public function equipes(int $divisionId): array
    {
        return $this->db->all('SELECT de.equipe_id, de.position, e.nom, e.golf_id FROM agca_division_equipe de JOIN agca_equipe e ON e.id = de.equipe_id WHERE de.division_id = ? ORDER BY de.position', [$divisionId]);
    }

    public function divisionDeLEquipe(int $equipeId, int $saisonId): ?array
    {
        return $this->db->one('SELECT d.* FROM agca_division d JOIN agca_division_equipe de ON de.division_id = d.id WHERE de.equipe_id = ? AND d.saison_id = ?', [$equipeId, $saisonId]);
    }
}
