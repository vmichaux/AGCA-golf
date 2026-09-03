<?php
declare(strict_types=1);
namespace Agca\App;

final class Session
{
    public function demarrer(): void
    {
        if (PHP_SAPI === 'cli') { $_SESSION ??= []; return; }
        if (session_status() === PHP_SESSION_ACTIVE) { return; }
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
        session_name('agca');
        session_start();
    }

    public function get(string $cle, mixed $defaut = null): mixed { return $_SESSION[$cle] ?? $defaut; }
    public function set(string $cle, mixed $valeur): void { $_SESSION[$cle] = $valeur; }
    public function supprimer(string $cle): void { unset($_SESSION[$cle]); }

    public function flash(string $type, string $message): void
    {
        $_SESSION['_flashs'][] = ['type' => $type, 'message' => $message];
    }

    /** @return list<array{type:string,message:string}> */
    public function consommerFlashs(): array
    {
        $f = $_SESSION['_flashs'] ?? [];
        unset($_SESSION['_flashs']);
        return $f;
    }

    public function csrf(): string
    {
        if (empty($_SESSION['_csrf'])) { $_SESSION['_csrf'] = bin2hex(random_bytes(16)); }
        return $_SESSION['_csrf'];
    }

    public function verifierCsrf(?string $jeton): bool
    {
        return is_string($jeton) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $jeton);
    }

    public function regenerer(): void
    {
        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE) { session_regenerate_id(true); }
    }

    public function detruire(): void
    {
        $_SESSION = [];
        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE) { session_destroy(); }
    }
}
