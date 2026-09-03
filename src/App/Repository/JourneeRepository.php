<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class JourneeRepository extends Repository
{
    public function creer(int $saisonId, int $serieId, int $numero, string $phase, string $date): int
    {
        return $this->db->insert('INSERT INTO agca_journee (saison_id, serie_id, numero, phase, date_calendrier) VALUES (?, ?, ?, ?, ?)', [$saisonId, $serieId, $numero, $phase, $date]);
    }

    public function parSaisonEtSerie(int $saisonId, int $serieId): array
    {
        return $this->db->all("SELECT * FROM agca_journee WHERE saison_id = ? AND serie_id = ? ORDER BY FIELD(phase, 'aller', 'retour'), numero", [$saisonId, $serieId]);
    }

    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_journee WHERE id = ?', [$id]); }
}
