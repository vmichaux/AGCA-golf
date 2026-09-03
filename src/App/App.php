<?php
declare(strict_types=1);
namespace Agca\App;

use Agca\App\Http\HttpException;
use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Http\Router;

final class App
{
    private ?Db $db = null;
    private ?Session $session = null;
    private ?View $view = null;
    private ?Router $router = null;
    private ?Auth $auth = null;
    /** @var array<string, object> */
    private array $services = [];

    /** @param array<string, mixed> $config */
    public function __construct(private array $config, private string $racine = '')
    {
        $this->racine = $racine !== '' ? $racine : dirname(__DIR__, 2);
    }

    public function config(string $chemin, mixed $defaut = null): mixed
    {
        $v = $this->config;
        foreach (explode('.', $chemin) as $k) {
            if (!is_array($v) || !array_key_exists($k, $v)) { return $defaut; }
            $v = $v[$k];
        }
        return $v;
    }

    public function racine(): string { return $this->racine; }
    public function db(): Db { return $this->db ??= Db::depuisConfig($this->config); }
    public function session(): Session { return $this->session ??= new Session(); }
    public function view(): View { return $this->view ??= new View($this->racine . '/templates'); }
    public function auth(): Auth { return $this->auth ??= new Auth($this); }

    public function router(): Router
    {
        if ($this->router === null) { $this->router = new Router(); Routes::declarer($this->router); }
        return $this->router;
    }

    /** Instancie une fois chaque dépôt/service : $app->service(SaisonRepository::class). */
    public function service(string $classe): object
    {
        return $this->services[$classe] ??= new $classe($this);
    }

    public function executer(Request $req): Response
    {
        $this->session()->demarrer();
        try {
            $route = $this->router()->resoudre($req->methode(), $req->chemin());
            if ($route === null) { throw new HttpException(404, 'Page introuvable'); }
            [$classe, $methode] = $route['handler'];
            $ctrl = new $classe($this);
            return $ctrl->$methode($req, ...array_values($route['params']));
        } catch (HttpException $e) {
            if ($e->statut === 302) { return Response::redirection($e->getMessage()); }
            return Response::html($this->view()->rendre('erreur', ['statut' => $e->statut, 'message' => $e->getMessage()]), $e->statut);
        } catch (\Throwable $e) {
            error_log((string) $e);
            $msg = $this->config('app.debug') ? (string) $e : 'Une erreur est survenue. L\'administrateur a été informé.';
            return Response::html($this->view()->rendre('erreur', ['statut' => 500, 'message' => $msg]), 500);
        }
    }
}
