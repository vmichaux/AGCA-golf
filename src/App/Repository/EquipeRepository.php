<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class EquipeRepository extends Repository
{
    private const SELECT = 'SELECT e.*, g.nom AS golf_nom FROM agca_equipe e JOIN agca_golf g ON g.id = e.golf_id';

    public function parId(int $id): ?array { return $this->db->one(self::SELECT . ' WHERE e.id = ?', [$id]); }

    public function parSerie(int $serieId, bool $actifSeulement = true): array
    {
        return $this->db->all(self::SELECT . ' WHERE e.serie_id = ?' . ($actifSeulement ? ' AND e.actif = 1' : '') . ' ORDER BY e.nom', [$serieId]);
    }

    public function parNomEtSerie(string $nom, int $serieId): ?array
    {
        return $this->db->one(self::SELECT . ' WHERE UPPER(e.nom) = UPPER(?) AND e.serie_id = ?', [trim($nom), $serieId]);
    }

    public function creer(array $champs): int
    {
        $champs += ['capitaine_nom' => null, 'capitaine_prenom' => null, 'capitaine_email' => null, 'capitaine_tel' => null, 'actif' => 1];
        [$set, $p] = $this->set($champs);
        return $this->db->insert("INSERT INTO agca_equipe SET $set", $p);
    }

    public function modifier(int $id, array $champs): void
    {
        [$set, $p] = $this->set($champs);
        $this->db->exec("UPDATE agca_equipe SET $set WHERE id = :id", $p + ['id' => $id]);
    }
}
