<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class PageRepository extends Repository
{
    private const CHAMPS = ['slug', 'titre', 'corps_md', 'corps_html', 'systeme', 'dans_menu', 'ordre', 'modifie_par'];

    public function parSlug(string $slug): ?array { return $this->db->one('SELECT * FROM agca_page WHERE slug = ?', [$slug]); }
    public function parId(int $id): ?array { return $this->db->one('SELECT * FROM agca_page WHERE id = ?', [$id]); }
    public function toutes(): array { return $this->db->all('SELECT * FROM agca_page ORDER BY ordre, slug'); }
    public function menu(): array { return $this->db->all('SELECT * FROM agca_page WHERE dans_menu = 1 ORDER BY ordre, titre'); }

    public function creer(array $champs): int
    {
        [$set, $p] = $this->set(array_intersect_key($champs, array_flip(self::CHAMPS)));
        return $this->db->insert("INSERT INTO agca_page SET $set", $p);
    }

    public function modifier(int $id, array $champs): void
    {
        $champs = array_intersect_key($champs, array_flip(self::CHAMPS));
        if ($champs === []) { return; }
        [$set, $p] = $this->set($champs);
        $this->db->exec("UPDATE agca_page SET $set WHERE id = :id", $p + ['id' => $id]);
    }

    public function supprimer(int $id): bool
    {
        $page = $this->parId($id);
        if ($page === null || (int) $page['systeme'] === 1) { return false; }
        $this->db->exec('DELETE FROM agca_page WHERE id = ?', [$id]);
        return true;
    }

    public function documents(int $pageId): array
    {
        return $this->db->all('SELECT d.*, pd.ordre AS ordre_page FROM agca_page_document pd JOIN agca_document d ON d.id = pd.document_id WHERE pd.page_id = ? ORDER BY pd.ordre, d.titre', [$pageId]);
    }

    /** @param list<int> $ids */
    public function definirDocuments(int $pageId, array $ids): void
    {
        $this->db->transaction(function () use ($pageId, $ids) {
            $this->db->exec('DELETE FROM agca_page_document WHERE page_id = ?', [$pageId]);
            foreach (array_values($ids) as $i => $docId) {
                $this->db->exec('INSERT INTO agca_page_document (page_id, document_id, ordre) VALUES (?, ?, ?)', [$pageId, (int) $docId, $i]);
            }
        });
    }
}
