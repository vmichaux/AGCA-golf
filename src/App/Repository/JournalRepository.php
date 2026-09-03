<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class JournalRepository extends Repository
{
    public function ecrire(?int $utilisateurId, string $action, ?string $cibleType, ?int $cibleId, array $detail = []): void
    {
        $this->db->exec('INSERT INTO agca_journal (utilisateur_id, action, cible_type, cible_id, detail) VALUES (?, ?, ?, ?, ?)',
            [$utilisateurId, $action, $cibleType, $cibleId, json_encode($detail, JSON_UNESCAPED_UNICODE)]);
    }

    public function recents(int $limite = 100): array
    {
        return $this->db->all('SELECT j.*, u.identifiant FROM agca_journal j LEFT JOIN agca_utilisateur u ON u.id = j.utilisateur_id ORDER BY j.id DESC LIMIT ' . (int) $limite);
    }

    public function parCible(string $type, int $id): array
    {
        return $this->db->all('SELECT j.*, u.identifiant FROM agca_journal j LEFT JOIN agca_utilisateur u ON u.id = j.utilisateur_id WHERE j.cible_type = ? AND j.cible_id = ? ORDER BY j.id DESC', [$type, $id]);
    }
}
