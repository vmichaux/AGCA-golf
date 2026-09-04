<?php
declare(strict_types=1);
namespace Agca\App\Repository;

final class RencontreRepository extends Repository
{
    private const SELECT = 'SELECT r.*, d.libelle AS division_libelle, d.saison_id, sa.statut AS saison_statut, d.serie_id, se.code AS serie_code,
        j.numero AS journee_numero, j.phase AS journee_phase, j.date_calendrier,
        er.nom AS recevant_nom, er.golf_id AS recevant_golf_id, er.capitaine_email AS recevant_email,
        ei.nom AS invite_nom, ei.golf_id AS invite_golf_id, ei.capitaine_email AS invite_email
        FROM agca_rencontre r
        JOIN agca_division d ON d.id = r.division_id
        JOIN agca_saison sa ON sa.id = d.saison_id
        JOIN agca_serie se ON se.id = d.serie_id
        JOIN agca_journee j ON j.id = r.journee_id
        JOIN agca_equipe er ON er.id = r.recevant_id
        JOIN agca_equipe ei ON ei.id = r.invite_id';
    private const ORDRE = " ORDER BY FIELD(j.phase, 'aller', 'retour'), j.numero, r.id";

    public function creer(int $divisionId, int $journeeId, int $recevantId, int $inviteId, string $dateReelle): int
    {
        return $this->db->insert('INSERT INTO agca_rencontre (division_id, journee_id, recevant_id, invite_id, date_reelle) VALUES (?, ?, ?, ?, ?)', [$divisionId, $journeeId, $recevantId, $inviteId, $dateReelle]);
    }

    public function parId(int $id): ?array { return $this->db->one(self::SELECT . ' WHERE r.id = ?', [$id]); }
    public function parDivision(int $divisionId): array { return $this->db->all(self::SELECT . ' WHERE r.division_id = ?' . self::ORDRE, [$divisionId]); }

    public function parEquipeEtSaison(int $equipeId, int $saisonId): array
    {
        return $this->db->all(self::SELECT . ' WHERE d.saison_id = ? AND (r.recevant_id = ? OR r.invite_id = ?)' . self::ORDRE, [$saisonId, $equipeId, $equipeId]);
    }

    public function mettreAJourResultat(int $id, array $champs): void
    {
        $permis = ['statut', 'forfaitaire_id', 'total_pour', 'total_contre', 'pts_rencontre_pour', 'pts_rencontre_contre', 'bonus_invite', 'alertes', 'alertes_vues', 'enregistree_le', 'enregistree_par'];
        $champs = array_intersect_key($champs, array_flip($permis));
        if (array_key_exists('alertes', $champs) && is_array($champs['alertes'])) {
            $champs['alertes'] = json_encode(array_values($champs['alertes']), JSON_UNESCAPED_UNICODE);
        }
        [$set, $p] = $this->set($champs);
        $this->db->exec("UPDATE agca_rencontre SET $set WHERE id = :id", $p + ['id' => $id]);
    }

    public function mettreAJourDate(int $id, string $date, bool $reportee): void
    {
        $this->db->exec('UPDATE agca_rencontre SET date_reelle = ?, reportee = ? WHERE id = ?', [$date, (int) $reportee, $id]);
    }

    /** Rencontres à jouer dont la date est passée depuis 48 h, saison active, sans relance récente. */
    public function aRelancer(string $limiteYmd, int $joursEntreRelances, ?string $reference = null): array
    {
        $reference ??= date('Y-m-d H:i:s');
        return $this->db->all(self::SELECT . " WHERE r.statut = 'a_jouer' AND sa.statut = 'active' AND r.date_reelle <= ?
            AND NOT EXISTS (SELECT 1 FROM agca_relance rl WHERE rl.rencontre_id = r.id AND rl.envoyee_le > DATE_SUB(?, INTERVAL ? DAY))" . self::ORDRE, [$limiteYmd, $reference, $joursEntreRelances]);
    }

    /** Prochaines rencontres à jouer de la saison, à partir d'une date incluse (une seule requête). */
    public function aVenir(int $saisonId, string $depuisYmd, int $limite): array
    {
        $limite = max(1, min(200, $limite));
        return $this->db->all(self::SELECT . " WHERE d.saison_id = ? AND r.statut = 'a_jouer' AND r.date_reelle >= ?
            ORDER BY r.date_reelle, se.ordre, d.ordre, d.id, r.id LIMIT " . $limite, [$saisonId, $depuisYmd]);
    }

    /** Première date à venir du calendrier réel de la saison (rencontres à jouer). */
    public function premiereDateAVenir(int $saisonId, string $depuisYmd): ?string
    {
        $l = $this->db->one("SELECT MIN(r.date_reelle) AS date_reelle FROM agca_rencontre r
            JOIN agca_division d ON d.id = r.division_id
            WHERE d.saison_id = ? AND r.statut = 'a_jouer' AND r.date_reelle >= ?", [$saisonId, $depuisYmd]);
        return $l === null || $l['date_reelle'] === null ? null : (string) $l['date_reelle'];
    }

    /**
     * Résumé par série des rencontres à jouer d'une date : nombre de rencontres, de divisions
     * et de journées distinctes. `nb_journees > 1` (report d'une rencontre sur la date d'une
     * autre journée) : le numéro et la phase ne caractérisent plus la date, ne pas les afficher.
     */
    public function resumeParSerieALaDate(int $saisonId, string $ymd): array
    {
        return $this->db->all("SELECT se.code, se.libelle, MIN(j.numero) AS journee_numero, MIN(j.phase) AS journee_phase,
            COUNT(*) AS nb_rencontres, COUNT(DISTINCT r.division_id) AS nb_divisions, COUNT(DISTINCT r.journee_id) AS nb_journees
            FROM agca_rencontre r
            JOIN agca_division d ON d.id = r.division_id
            JOIN agca_serie se ON se.id = d.serie_id
            JOIN agca_journee j ON j.id = r.journee_id
            WHERE d.saison_id = ? AND r.statut = 'a_jouer' AND r.date_reelle = ?
            GROUP BY se.id, se.code, se.libelle, se.ordre ORDER BY se.ordre", [$saisonId, $ymd]);
    }

    public function avecAlertes(int $saisonId): array
    {
        return $this->db->all(self::SELECT . " WHERE d.saison_id = ? AND r.alertes_vues = 0 AND r.alertes IS NOT NULL AND JSON_LENGTH(r.alertes) > 0" . self::ORDRE, [$saisonId]);
    }

    public function marquerAlertesVues(int $id): void
    {
        $this->db->exec('UPDATE agca_rencontre SET alertes_vues = 1 WHERE id = ?', [$id]);
    }
}
