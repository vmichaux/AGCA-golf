<?php
declare(strict_types=1);
namespace Agca\App;

use Agca\App\Controller\ApiController;
use Agca\App\Controller\AuthController;
use Agca\App\Controller\CapitaineController;
use Agca\App\Controller\FeuilleController;
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

        $r->get('/capitaine', [CapitaineController::class, 'tableauDeBord']);
        $r->get('/rencontre/{id}', [FeuilleController::class, 'lecture']);
        $r->get('/rencontre/{id}/saisie', [FeuilleController::class, 'formulaireSaisie']);
        $r->post('/rencontre/{id}/saisie', [FeuilleController::class, 'enregistrer']);
        $r->post('/rencontre/{id}/date', [FeuilleController::class, 'changerDate']);
        $r->get('/api/joueurs', [ApiController::class, 'joueurs']);
    }
}
