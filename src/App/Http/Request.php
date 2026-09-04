<?php
declare(strict_types=1);
namespace Agca\App\Http;

final class Request
{
    public function __construct(
        private string $methode, private string $chemin, private array $get, private array $post, private string $ip,
        private array $fichiers = [], private bool $estHead = false,
    ) {}

    /** HEAD est traitée comme GET (mêmes routes), en retenant qu'aucun corps ne doit être émis. */
    public static function depuisGlobales(): self
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $chemin = parse_url($uri, PHP_URL_PATH) ?: '/';
        $methode = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $estHead = $methode === 'HEAD';
        if ($estHead) { $methode = 'GET'; }
        return new self($methode, $chemin, $_GET, $_POST, (string) ($_SERVER['REMOTE_ADDR'] ?? ''), $_FILES, $estHead);
    }

    public function methode(): string { return $this->methode; }
    public function chemin(): string { return $this->chemin; }
    public function estPost(): bool { return $this->methode === 'POST'; }
    public function estHead(): bool { return $this->estHead; }
    public function ip(): string { return $this->ip; }
    public function get(string $cle, mixed $defaut = null): mixed { return $this->get[$cle] ?? $defaut; }
    public function post(string $cle, mixed $defaut = null): mixed { return $this->post[$cle] ?? $defaut; }
    /** @return array<string, mixed> */
    public function tousPost(): array { return $this->post; }

    /** Entrée de $_FILES pour ce champ, ou null. */
    public function fichier(string $cle): ?array
    {
        $f = $this->fichiers[$cle] ?? null;
        return is_array($f) && isset($f['tmp_name']) ? $f : null;
    }
}
