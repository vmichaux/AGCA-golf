<?php
declare(strict_types=1);
namespace Agca\App\Repository;

/** Limitation par adresse IP des envois du formulaire de contact (indépendante de la session). */
final class ContactLimiteRepository extends Repository
{
    public function compterDepuis(string $ip, string $depuis): int
    {
        return (int) $this->db->one('SELECT COUNT(*) AS n FROM agca_contact_limite WHERE ip = ? AND quand >= ?', [$ip, $depuis])['n'];
    }

    public function compterGlobalDepuis(string $depuis): int
    {
        return (int) $this->db->one('SELECT COUNT(*) AS n FROM agca_contact_limite WHERE quand >= ?', [$depuis])['n'];
    }

    public function enregistrer(string $ip): void
    {
        $this->db->exec('INSERT INTO agca_contact_limite (ip, quand) VALUES (?, NOW())', [$ip]);
    }

    /** Supprime les lignes antérieures à `$avant` (« Y-m-d H:i:s »). */
    public function purger(string $avant): void
    {
        $this->db->exec('DELETE FROM agca_contact_limite WHERE quand < ?', [$avant]);
    }
}
