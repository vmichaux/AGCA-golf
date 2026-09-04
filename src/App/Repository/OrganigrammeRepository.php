<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class OrganigrammeRepository extends Repository
{
    private const CHAMPS = ['groupe', 'fonction', 'prenom', 'nom', 'golf', 'ordre'];

    /** @return array{bureau: list<array>, ca: list<array>} */
    public function parGroupe(): array
    {
        $out = ['bureau' => [], 'ca' => []];
        foreach ($this->tous() as $l) { $out[$l['groupe']][] = $l; }
        return $out;
    }
    public function tous(): array { return $this->db->all("SELECT * FROM agca_organigramme ORDER BY FIELD(groupe, 'bureau', 'ca'), ordre, nom"); }
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_organigramme WHERE id = ?', [$id]); }
    public function creer(array $champs): int
    {
        [$set, $p] = $this->set(array_intersect_key($champs, array_flip(self::CHAMPS)));
        return $this->db->insert("INSERT INTO agca_organigramme SET $set", $p);
    }
    public function modifier(int $id, array $champs): void
    {
        $champs = array_intersect_key($champs, array_flip(self::CHAMPS));
        if ($champs === []) { return; }
        [$set, $p] = $this->set($champs);
        $this->db->exec("UPDATE agca_organigramme SET $set WHERE id = :id", $p + ['id' => $id]);
    }
    public function supprimer(int $id): void { $this->db->exec('DELETE FROM agca_organigramme WHERE id = ?', [$id]); }
}
