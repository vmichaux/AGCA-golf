<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class RelanceRepository extends Repository
{
    public function enregistrer(int $rencontreId): void
    {
        $this->db->exec('INSERT INTO agca_relance (rencontre_id) VALUES (?)', [$rencontreId]);
    }

    public function derniere(int $rencontreId): ?string
    {
        $l = $this->db->one('SELECT MAX(envoyee_le) AS d FROM agca_relance WHERE rencontre_id = ?', [$rencontreId]);
        return $l['d'] ?? null;
    }
}
