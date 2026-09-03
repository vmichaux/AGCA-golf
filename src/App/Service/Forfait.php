<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Http\HttpException;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\PartieRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SerieRepository;
use Agca\Domain\CalculRencontre;

final class Forfait
{
    public function __construct(private App $app) {}

    public function declarer(int $rencontreId, string $camp, array $admin): void
    {
        $r = $this->garde($rencontreId, $admin);
        if (!in_array($camp, ['recevant', 'invite'], true)) { throw new HttpException(400, 'Équipe forfaitaire inconnue'); }
        $serie = $this->app->service(SerieRepository::class)->parId((int) $r['serie_id'])['serie'];
        $calcul = CalculRencontre::calculer($serie, [], $camp);
        $forfaitaire = $camp === 'recevant' ? (int) $r['recevant_id'] : (int) $r['invite_id'];
        $partiesEffacees = $this->app->service(PartieRepository::class)->parRencontre($rencontreId);
        $this->app->db()->transaction(function () use ($r, $rencontreId, $calcul, $forfaitaire, $admin, $partiesEffacees) {
            $this->app->service(PartieRepository::class)->remplacer($rencontreId, []);
            $this->app->service(RencontreRepository::class)->mettreAJourResultat($rencontreId, ['statut' => 'forfait', 'forfaitaire_id' => $forfaitaire,
                'total_pour' => $calcul->totalPour, 'total_contre' => $calcul->totalContre, 'pts_rencontre_pour' => $calcul->ptsPour, 'pts_rencontre_contre' => $calcul->ptsContre,
                'bonus_invite' => $calcul->bonusInvite, 'alertes' => [], 'alertes_vues' => 1, 'enregistree_le' => date('Y-m-d H:i:s'), 'enregistree_par' => (int) $admin['id']]);
            $this->app->service(JournalRepository::class)->ecrire((int) $admin['id'], 'forfait', 'rencontre', $rencontreId,
                ['forfaitaire' => $forfaitaire, 'statut_precedent' => $r['statut'], 'parties_effacees' => $partiesEffacees]);
        });
        $this->app->service(Notifications::class)->forfait($this->app->service(RencontreRepository::class)->parId($rencontreId), false, $admin);
    }

    public function annuler(int $rencontreId, array $admin): void
    {
        $r = $this->garde($rencontreId, $admin);
        if ($r['statut'] !== 'forfait') { throw new HttpException(400, 'Cette rencontre n\'est pas en forfait'); }
        $this->app->db()->transaction(function () use ($rencontreId, $admin) {
            $this->app->service(RencontreRepository::class)->mettreAJourResultat($rencontreId, ['statut' => 'a_jouer', 'forfaitaire_id' => null,
                'total_pour' => 0, 'total_contre' => 0, 'pts_rencontre_pour' => 0, 'pts_rencontre_contre' => 0, 'bonus_invite' => 0, 'alertes' => [], 'alertes_vues' => 1,
                'enregistree_le' => null, 'enregistree_par' => null]);
            $this->app->service(JournalRepository::class)->ecrire((int) $admin['id'], 'forfait_annule', 'rencontre', $rencontreId);
        });
        $this->app->service(Notifications::class)->forfait($this->app->service(RencontreRepository::class)->parId($rencontreId), true, $admin);
    }

    private function garde(int $rencontreId, array $u): array
    {
        if (empty($u['est_admin'])) { throw new HttpException(403, 'Réservé à l\'administrateur'); }
        $r = $this->app->service(RencontreRepository::class)->parId($rencontreId) ?? throw new HttpException(404, 'Rencontre introuvable');
        if ($r['saison_statut'] !== 'active') { throw new HttpException(403, 'Saison gelée'); }
        return $r;
    }
}
