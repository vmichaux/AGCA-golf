<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Service\Saisons;

final class CapitaineController extends Controller
{
    public function tableauDeBord(Request $req): Response
    {
        $u = $this->exigerConnexion();
        if ($u['est_admin'] && $u['equipe_id'] === null) { return $this->rediriger('/admin'); }
        $saison = $this->app->service(Saisons::class)->courante(null);
        $equipe = $this->app->service(EquipeRepository::class)->parId((int) $u['equipe_id']);
        if ($equipe === null || $saison === null) {
            return $this->rendre('capitaine/tableau_de_bord', ['titre' => 'Mon équipe', 'equipe' => $equipe, 'saison' => $saison, 'division' => null, 'rencontres' => []]);
        }
        $rencontres = $this->app->service(RencontreRepository::class)->parEquipeEtSaison((int) $equipe['id'], (int) $saison['id']);
        foreach ($rencontres as &$r) {
            $r['domicile'] = (int) $r['recevant_id'] === (int) $equipe['id'];
            $r['etat'] = match (true) {
                $r['statut'] === 'forfait' => 'Forfait',
                $r['statut'] === 'enregistree' => 'Enregistrée',
                $r['domicile'] => 'À saisir',
                default => 'En attente de l\'adversaire',
            };
        }
        unset($r);
        return $this->rendre('capitaine/tableau_de_bord', ['titre' => 'Mon équipe — ' . $equipe['nom'], 'equipe' => $equipe, 'saison' => $saison,
            'division' => $this->app->service(DivisionRepository::class)->divisionDeLEquipe((int) $equipe['id'], (int) $saison['id']), 'rencontres' => $rencontres]);
    }
}
