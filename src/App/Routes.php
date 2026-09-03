<?php
declare(strict_types=1);
namespace Agca\App;

use Agca\App\Controller\AdminController;
use Agca\App\Controller\ApiController;
use Agca\App\Controller\AuthController;
use Agca\App\Controller\CapitaineController;
use Agca\App\Controller\FeuilleController;
use Agca\App\Controller\PublicController;
use Agca\App\Controller\SaisonController;
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

        $r->get('/admin', [AdminController::class, 'index']);
        $r->get('/admin/saisons', [SaisonController::class, 'liste']);
        $r->post('/admin/saisons', [SaisonController::class, 'creer']);
        $r->get('/admin/saisons/{id}', [SaisonController::class, 'detail']);
        $r->post('/admin/saisons/{id}/divisions', [SaisonController::class, 'ajouterDivision']);
        $r->post('/admin/saisons/{id}/geler', [SaisonController::class, 'geler']);
        $r->post('/admin/saisons/{id}/activer', [SaisonController::class, 'activer']);
        $r->get('/admin/alertes', [AdminController::class, 'alertes']);
        $r->post('/admin/rencontre/{id}/alertes-vues', [AdminController::class, 'alertesVues']);
        $r->post('/admin/rencontre/{id}/forfait', [AdminController::class, 'forfait']);
        $r->post('/admin/rencontre/{id}/annuler-forfait', [AdminController::class, 'annulerForfait']);
        $r->get('/admin/utilisateurs', [AdminController::class, 'utilisateurs']);
        $r->post('/admin/utilisateurs', [AdminController::class, 'creerUtilisateur']);
        $r->post('/admin/utilisateurs/{id}/reinitialiser', [AdminController::class, 'reinitialiser']);
        $r->post('/admin/utilisateurs/{id}/admin', [AdminController::class, 'basculerAdmin']);
        $r->post('/admin/utilisateurs/{id}/rattacher', [AdminController::class, 'rattacher']);
        $r->get('/admin/joueurs', [AdminController::class, 'joueurs']);
        $r->post('/admin/joueurs/fusion', [AdminController::class, 'fusionnerJoueurs']);
        $r->post('/admin/joueurs/{id}', [AdminController::class, 'modifierJoueur']);
    }
}
