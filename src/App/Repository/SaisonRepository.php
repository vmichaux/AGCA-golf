<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class SaisonRepository extends Repository
{
    public function active(): ?array { return $this->db->one("SELECT * FROM agca_saison WHERE statut = 'active' ORDER BY date_debut DESC LIMIT 1"); }
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_saison WHERE id = ?', [$id]); }
    public function toutes(): array { return $this->db->all('SELECT * FROM agca_saison ORDER BY date_debut DESC'); }

    public function creer(string $libelle, string $debut, string $fin): int
    {
        return $this->db->insert('INSERT INTO agca_saison (libelle, date_debut, date_fin, statut) VALUES (?, ?, ?, ?)', [$libelle, $debut, $fin, 'active']);
    }

    public function changerStatut(int $id, string $statut): void
    {
        $this->db->exec('UPDATE agca_saison SET statut = ? WHERE id = ?', [$statut, $id]);
    }
}
