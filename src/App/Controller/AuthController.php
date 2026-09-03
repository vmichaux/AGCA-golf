<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\SerieRepository;

final class AuthController extends Controller
{
    public function formulaireConnexion(Request $req): Response
    {
        if ($this->app->auth()->estConnecte()) { return $this->rediriger('/capitaine'); }
        return $this->rendre('auth/connexion', ['titre' => 'Connexion', 'series' => $this->app->service(SerieRepository::class)->toutes(), 'identifiant' => '', 'serie' => 'M']);
    }

    public function connexion(Request $req): Response
    {
        $this->exigerCsrf($req);
        $r = $this->app->auth()->connecter((string) $req->post('identifiant', ''), (string) $req->post('serie', ''), (string) $req->post('mot_de_passe', ''));
        if (!$r['ok']) {
            return $this->rendre('auth/connexion', ['titre' => 'Connexion', 'series' => $this->app->service(SerieRepository::class)->toutes(),
                'identifiant' => (string) $req->post('identifiant', ''), 'serie' => (string) $req->post('serie', 'M'), 'erreur' => $r['erreur']], 401);
        }
        $this->flash('succes', 'Bienvenue.');
        return $this->rediriger($this->app->auth()->estAdmin() ? '/admin' : '/capitaine');
    }

    public function deconnexion(Request $req): Response
    {
        $this->exigerCsrf($req);
        $this->app->auth()->deconnecter();
        return $this->rediriger('/');
    }

    public function formulaireMotDePasse(Request $req): Response
    {
        $this->exigerConnexion();
        return $this->rendre('auth/mot_de_passe', ['titre' => 'Changer mon mot de passe']);
    }

    public function motDePasse(Request $req): Response
    {
        $u = $this->exigerConnexion();
        $this->exigerCsrf($req);
        if ($req->post('nouveau') !== $req->post('confirmation')) {
            return $this->rendre('auth/mot_de_passe', ['titre' => 'Changer mon mot de passe', 'erreur' => 'La confirmation ne correspond pas.']);
        }
        $erreur = $this->app->auth()->changerMotDePasse((int) $u['id'], (string) $req->post('actuel', ''), (string) $req->post('nouveau', ''));
        if ($erreur !== null) { return $this->rendre('auth/mot_de_passe', ['titre' => 'Changer mon mot de passe', 'erreur' => $erreur]); }
        $this->flash('succes', 'Mot de passe modifié.');
        return $this->rediriger('/capitaine');
    }
}
