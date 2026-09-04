<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class CompetitionRepository extends Repository
{
    private const CHAMPS = ['code', 'nom', 'accroche', 'formule', 'corps_md', 'corps_html', 'ordre', 'actif'];
    private const CHAMPS_MODIFIER = ['nom', 'accroche', 'formule', 'corps_md', 'corps_html', 'ordre', 'actif'];
    private const CHAMPS_PALMARES = ['competition_id', 'saison', 'lieu', 'vainqueur', 'detail_md', 'detail_html', 'document_id', 'ordre'];
    private const SELECT_PALMARES = 'SELECT p.*, d.titre AS document_titre, d.fichier AS document_fichier FROM agca_palmares p LEFT JOIN agca_document d ON d.id = p.document_id';

    public function toutes(bool $activesSeulement = true): array
    {
        return $this->db->all('SELECT * FROM agca_competition' . ($activesSeulement ? ' WHERE actif = 1' : '') . ' ORDER BY ordre, nom');
    }
    public function parCode(string $code): ?array { return $this->db->one('SELECT * FROM agca_competition WHERE code = ?', [$code]); }
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_competition WHERE id = ?', [$id]); }

    public function creer(array $champs): int
    {
        [$set, $p] = $this->set(array_intersect_key($champs, array_flip(self::CHAMPS)));
        return $this->db->insert("INSERT INTO agca_competition SET $set", $p);
    }

    public function modifier(int $id, array $champs): void
    {
        $champs = array_intersect_key($champs, array_flip(self::CHAMPS_MODIFIER));
        if ($champs === []) { return; }
        [$set, $p] = $this->set($champs);
        $this->db->exec("UPDATE agca_competition SET $set WHERE id = :id", $p + ['id' => $id]);
    }

    public function palmares(int $competitionId): array
    {
        return $this->db->all(self::SELECT_PALMARES . ' WHERE p.competition_id = ? ORDER BY p.saison DESC, p.ordre DESC, p.id DESC', [$competitionId]);
    }
    public function dernierPalmares(int $competitionId): ?array { return $this->palmares($competitionId)[0] ?? null; }
    public function palmaresParId(int $id): ?array { return $this->db->one(self::SELECT_PALMARES . ' WHERE p.id = ?', [$id]); }

    public function ajouterPalmares(array $champs): int
    {
        [$set, $p] = $this->set(array_intersect_key($champs, array_flip(self::CHAMPS_PALMARES)));
        return $this->db->insert("INSERT INTO agca_palmares SET $set", $p);
    }

    public function modifierPalmares(int $id, array $champs): void
    {
        $champs = array_intersect_key($champs, array_flip(self::CHAMPS_PALMARES));
        if ($champs === []) { return; }
        [$set, $p] = $this->set($champs);
        $this->db->exec("UPDATE agca_palmares SET $set WHERE id = :id", $p + ['id' => $id]);
    }

    public function supprimerPalmares(int $id): void { $this->db->exec('DELETE FROM agca_palmares WHERE id = ?', [$id]); }
}
