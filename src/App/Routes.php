<?php
declare(strict_types=1);
namespace Agca\App;

use Agca\App\Controller\AuthController;
use Agca\App\Controller\PublicController;
use Agca\App\Http\Router;

final class Routes
{
    public static function declarer(Router $r): void
    {
        $r->get('/', [PublicController::class, 'accueil']);

        $r->get('/connexion', [AuthController::class, 'formulaireConnexion']);
        $r->post('/connexion', [AuthController::class, 'connexion']);
        $r->post('/deconnexion', [AuthController::class, 'deconnexion']);
        $r->get('/mot-de-passe', [AuthController::class, 'formulaireMotDePasse']);
        $r->post('/mot-de-passe', [AuthController::class, 'motDePasse']);

        $r->get('/serie/{code}/classements', [PublicController::class, 'classements']);
        $r->get('/serie/{code}/suivi', [PublicController::class, 'suivi']);
        $r->get('/serie/{code}/feuille-vierge', [PublicController::class, 'feuilleVierge']);
        $r->get('/serie/{code}/reglement', [PublicController::class, 'reglement']);
    }
}
