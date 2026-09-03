<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class GolfRepository extends Repository
{
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_golf WHERE id = ?', [$id]); }
    public function parNom(string $nom): ?array { return $this->db->one('SELECT * FROM agca_golf WHERE UPPER(nom) = UPPER(?)', [trim($nom)]); }
    public function tous(): array { return $this->db->all('SELECT * FROM agca_golf ORDER BY nom'); }

    public function trouverOuCreer(string $nom, ?string $ville = null): int
    {
        $nom = trim($nom);
        $g = $this->parNom($nom);
        if ($g !== null) { return (int) $g['id']; }
        return $this->db->insert('INSERT INTO agca_golf (nom, ville) VALUES (?, ?)', [mb_strtoupper($nom), $ville]);
    }
}
