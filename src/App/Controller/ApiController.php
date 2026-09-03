<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\JoueurRepository;

final class ApiController extends Controller
{
    public function joueurs(Request $req): Response
    {
        $this->exigerConnexion();
        $golf = (int) $req->get('golf', 0);
        if ($golf <= 0) { return Response::json([]); }
        $liste = $this->app->service(JoueurRepository::class)->chercher($golf, (string) $req->get('q', ''), 15);
        return Response::json(array_map(fn($j) => ['id' => (int) $j['id'], 'nom' => $j['nom'], 'sexe' => $j['sexe'], 'dernier_index' => $j['dernier_index'] === null ? null : (float) $j['dernier_index']], $liste));
    }
}
