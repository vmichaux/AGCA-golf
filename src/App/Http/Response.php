<?php
declare(strict_types=1);
namespace Agca\App\Http;

final class Response
{
    /** @param array<string,string> $entetes */
    public function __construct(public readonly int $statut, public readonly string $corps, public readonly array $entetes = []) {}

    public static function html(string $corps, int $statut = 200): self
    {
        return new self($statut, $corps, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function redirection(string $url): self
    {
        return new self(302, '', ['Location' => $url]);
    }

    public static function json(mixed $donnees, int $statut = 200): self
    {
        return new self($statut, json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /** @param bool $sansCorps requête HEAD : les en-têtes sont émis, jamais le corps. */
    public function envoyer(bool $sansCorps = false): void
    {
        http_response_code($this->statut);
        foreach ($this->entetes as $k => $v) { header("$k: $v"); }
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
        if (!$sansCorps) { echo $this->corps; }
    }
}
