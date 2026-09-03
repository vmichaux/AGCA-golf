<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\PartieRepository;
use Agca\App\Service\AccesRencontre;

final class FeuilleController extends Controller
{
    public function lecture(Request $req, string $id): Response
    {
        $u = $this->exigerConnexion();
        $acces = $this->app->service(AccesRencontre::class);
        $r = $acces->charger((int) $id, $u);
        return $this->rendre('feuille/lecture', [
            'titre' => 'Feuille ' . $r['recevant_nom'] . ' – ' . $r['invite_nom'],
            'rencontre' => $r,
            'parties' => $this->app->service(PartieRepository::class)->parRencontre((int) $id),
            'peutSaisir' => $acces->peutSaisir($r, $u),
            'peutModifierDate' => $acces->peutModifierDate($r, $u),
            'journal' => $u['est_admin'] ? $this->app->service(JournalRepository::class)->parCible('rencontre', (int) $id) : [],
        ]);
    }
}
