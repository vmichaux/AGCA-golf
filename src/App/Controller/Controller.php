<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\App;
use Agca\App\Http\HttpException;
use Agca\App\Http\Request;
use Agca\App\Http\Response;

abstract class Controller
{
    public function __construct(protected App $app) {}

    /** @param array<string, mixed> $vars */
    protected function rendre(string $template, array $vars = [], int $statut = 200): Response
    {
        $v = $this->app->view();
        $v->partager('utilisateur', $this->app->auth()->utilisateur());
        $v->partager('flashs', $this->app->session()->consommerFlashs());
        $v->partager('csrf', $this->app->session()->csrf());
        $v->partager('base_url', (string) $this->app->config('app.base_url', ''));
        return Response::html($v->rendre($template, $vars), $statut);
    }

    protected function rediriger(string $url): Response { return Response::redirection($url); }

    protected function flash(string $type, string $message): void { $this->app->session()->flash($type, $message); }

    protected function exigerCsrf(Request $req): void
    {
        if (!$this->app->session()->verifierCsrf($req->post('_csrf'))) {
            throw new HttpException(400, 'Formulaire expiré, merci de réessayer.');
        }
    }

    protected function introuvable(string $message = 'Page introuvable'): never
    {
        throw new HttpException(404, $message);
    }

    protected function interdit(string $message = 'Accès refusé'): never
    {
        throw new HttpException(403, $message);
    }

    /** @return array<string, mixed> utilisateur connecté */
    protected function exigerConnexion(): array
    {
        $u = $this->app->auth()->utilisateur();
        if ($u === null) {
            $this->app->session()->flash('info', 'Merci de vous connecter.');
            throw new HttpException(302, '/connexion');
        }
        return $u;
    }

    /** @return array<string, mixed> */
    protected function exigerAdmin(): array
    {
        $u = $this->exigerConnexion();
        if (!$u['est_admin']) { $this->interdit('Réservé à l\'administrateur.'); }
        return $u;
    }
}
