<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class AlbumRepository extends Repository
{
    private const CHAMPS = ['titre', 'annee', 'url', 'ordre'];

    public function tous(): array { return $this->db->all('SELECT * FROM agca_album ORDER BY annee DESC, ordre, titre'); }
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_album WHERE id = ?', [$id]); }
    public function creer(array $champs): int
    {
        [$set, $p] = $this->set(array_intersect_key($champs, array_flip(self::CHAMPS)));
        return $this->db->insert("INSERT INTO agca_album SET $set", $p);
    }
    public function modifier(int $id, array $champs): void
    {
        $champs = array_intersect_key($champs, array_flip(self::CHAMPS));
        if ($champs === []) { return; }
        [$set, $p] = $this->set($champs);
        $this->db->exec("UPDATE agca_album SET $set WHERE id = :id", $p + ['id' => $id]);
    }
    public function supprimer(int $id): void { $this->db->exec('DELETE FROM agca_album WHERE id = ?', [$id]); }
}
