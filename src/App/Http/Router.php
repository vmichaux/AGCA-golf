<?php
declare(strict_types=1);
namespace Agca\App\Http;

final class Router
{
    /** @var list<array{methode:string, regex:string, handler:array}> */
    private array $routes = [];

    public function get(string $motif, array $handler): void { $this->ajouter('GET', $motif, $handler); }
    public function post(string $motif, array $handler): void { $this->ajouter('POST', $motif, $handler); }

    private function ajouter(string $methode, string $motif, array $handler): void
    {
        $regex = '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', rtrim($motif, '/') ?: '/') . '$#';
        $this->routes[] = ['methode' => $methode, 'regex' => $regex, 'handler' => $handler];
    }

    /** @return array{handler:array, params:array<string,string>}|null */
    public function resoudre(string $methode, string $chemin): ?array
    {
        $chemin = rtrim($chemin, '/') ?: '/';
        foreach ($this->routes as $r) {
            if ($r['methode'] !== $methode) { continue; }
            if (preg_match($r['regex'], $chemin, $m)) {
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                return ['handler' => $r['handler'], 'params' => array_map('rawurldecode', $params)];
            }
        }
        return null;
    }
}
