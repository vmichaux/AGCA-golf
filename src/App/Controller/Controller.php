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
}
