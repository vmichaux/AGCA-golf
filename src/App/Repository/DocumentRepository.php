<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class DocumentRepository extends Repository
{
    private const CHAMPS = ['titre', 'fichier', 'type_mime', 'taille', 'categorie', 'televerse_par'];

    public function tous(): array { return $this->db->all('SELECT * FROM agca_document ORDER BY televerse_le DESC, id DESC'); }
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_document WHERE id = ?', [$id]); }
    public function parCategorie(string $categorie): array { return $this->db->all('SELECT * FROM agca_document WHERE categorie = ? ORDER BY televerse_le DESC', [$categorie]); }
    public function creer(array $champs): int
    {
        [$set, $p] = $this->set(array_intersect_key($champs, array_flip(self::CHAMPS)));
        return $this->db->insert("INSERT INTO agca_document SET $set", $p);
    }
    public function supprimer(int $id): void { $this->db->exec('DELETE FROM agca_document WHERE id = ?', [$id]); }
    public function estUtilise(int $id): bool
    {
        $n = $this->db->one('SELECT (SELECT COUNT(*) FROM agca_palmares WHERE document_id = ?) + (SELECT COUNT(*) FROM agca_page_document WHERE document_id = ?) AS n', [$id, $id]);
        return (int) $n['n'] > 0;
    }
}
