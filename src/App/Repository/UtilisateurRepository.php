<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class UtilisateurRepository extends Repository
{
    private const SELECT = 'SELECT u.*, e.nom AS equipe_nom, s.code AS serie_code FROM agca_utilisateur u LEFT JOIN agca_equipe e ON e.id = u.equipe_id LEFT JOIN agca_serie s ON s.id = u.serie_id';

    public function parId(int $id): ?array { return $this->db->one(self::SELECT . ' WHERE u.id = ?', [$id]); }
    public function tous(): array { return $this->db->all(self::SELECT . ' ORDER BY u.est_admin DESC, s.ordre, u.identifiant'); }
    public function parEquipe(int $equipeId): ?array { return $this->db->one(self::SELECT . ' WHERE u.equipe_id = ?', [$equipeId]); }

    public function parIdentifiantEtSerie(string $identifiant, ?int $serieId): ?array
    {
        $identifiant = trim($identifiant);
        if ($serieId !== null) {
            $u = $this->db->one(self::SELECT . ' WHERE UPPER(u.identifiant) = UPPER(?) AND u.serie_id = ?', [$identifiant, $serieId]);
            if ($u !== null) { return $u; }
        }
        return $this->db->one(self::SELECT . ' WHERE UPPER(u.identifiant) = UPPER(?) AND u.serie_id IS NULL', [$identifiant]);
    }

    public function creer(array $champs): int
    {
        $champs += ['equipe_id' => null, 'hash_sha1' => null, 'hash_bcrypt' => null, 'est_admin' => 0, 'nom_affiche' => null, 'email' => null];
        [$set, $p] = $this->set($champs);
        return $this->db->insert("INSERT INTO agca_utilisateur SET $set", $p);
    }

    public function enregistrerEchec(int $id, ?string $bloqueJusqua): void
    {
        $this->db->exec('UPDATE agca_utilisateur SET tentatives = tentatives + 1, bloque_jusqua = ? WHERE id = ?', [$bloqueJusqua, $id]);
    }

    public function enregistrerSucces(int $id): void
    {
        $this->db->exec('UPDATE agca_utilisateur SET tentatives = 0, bloque_jusqua = NULL, derniere_connexion = NOW() WHERE id = ?', [$id]);
    }

    public function definirBcrypt(int $id, string $hash): void
    {
        $this->db->exec('UPDATE agca_utilisateur SET hash_bcrypt = ?, hash_sha1 = NULL WHERE id = ?', [$hash, $id]);
    }

    public function definirAdmin(int $id, bool $admin): void
    {
        $this->db->exec('UPDATE agca_utilisateur SET est_admin = ? WHERE id = ?', [(int) $admin, $id]);
    }

    public function rattacher(int $id, ?int $equipeId, ?int $serieId): void
    {
        $this->db->exec('UPDATE agca_utilisateur SET equipe_id = ?, serie_id = ? WHERE id = ?', [$equipeId, $serieId, $id]);
    }
}
