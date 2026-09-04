<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class ActualiteRepository extends Repository
{
    private const CHAMPS = ['titre', 'slug', 'date_publication', 'resume', 'corps_md', 'corps_html', 'publie', 'modifie_par'];

    public function publiees(int $limite = 10, int $decalage = 0): array
    {
        return $this->db->all('SELECT * FROM agca_actualite WHERE publie = 1 AND date_publication <= CURDATE() ORDER BY date_publication DESC, id DESC LIMIT ' . (int) $limite . ' OFFSET ' . (int) $decalage);
    }
    public function compterPubliees(): int { return (int) $this->db->one('SELECT COUNT(*) AS n FROM agca_actualite WHERE publie = 1 AND date_publication <= CURDATE()')['n']; }
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_actualite WHERE id = ?', [$id]); }
    public function toutes(): array { return $this->db->all('SELECT * FROM agca_actualite ORDER BY date_publication DESC, id DESC'); }

    public function creer(array $champs): int
    {
        [$set, $p] = $this->set(array_intersect_key($champs, array_flip(self::CHAMPS)));
        return $this->db->insert("INSERT INTO agca_actualite SET $set", $p);
    }

    public function modifier(int $id, array $champs): void
    {
        $champs = array_intersect_key($champs, array_flip(self::CHAMPS));
        if ($champs === []) { return; }
        [$set, $p] = $this->set($champs);
        $this->db->exec("UPDATE agca_actualite SET $set WHERE id = :id", $p + ['id' => $id]);
    }

    public function supprimer(int $id): void { $this->db->exec('DELETE FROM agca_actualite WHERE id = ?', [$id]); }
}
