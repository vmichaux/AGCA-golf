<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\JoueurRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Service\Saisons;

final class ApiController extends Controller
{
    public function joueurs(Request $req): Response
    {
        $u = $this->exigerConnexion();
        $golf = (int) $req->get('golf', 0);
        if ($golf <= 0) { return Response::json([]); }
        if (!$u['est_admin'] && !$this->golfAutorise($golf, $u)) {
            return Response::json([], 403);
        }
        $liste = $this->app->service(JoueurRepository::class)->chercher($golf, (string) $req->get('q', ''), 15);
        return Response::json(array_map(fn($j) => ['id' => (int) $j['id'], 'nom' => $j['nom'], 'sexe' => $j['sexe'], 'dernier_index' => $j['dernier_index'] === null ? null : (float) $j['dernier_index']], $liste));
    }

    /** Un capitaine ne peut interroger que le golf de sa propre équipe, ou celui d'un adversaire de la saison en cours. */
    private function golfAutorise(int $golf, array $u): bool
    {
        $equipeId = (int) ($u['equipe_id'] ?? 0);
        if ($equipeId === 0) { return false; }
        $equipe = $this->app->service(EquipeRepository::class)->parId($equipeId);
        if ($equipe === null) { return false; }
        if ((int) $equipe['golf_id'] === $golf) { return true; }
        $saison = $this->app->service(Saisons::class)->courante(null);
        if ($saison === null) { return false; }
        foreach ($this->app->service(RencontreRepository::class)->parEquipeEtSaison($equipeId, (int) $saison['id']) as $r) {
            if ((int) $r['recevant_golf_id'] === $golf || (int) $r['invite_golf_id'] === $golf) { return true; }
        }
        return false;
    }
}
