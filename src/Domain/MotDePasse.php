<?php
declare(strict_types=1);
namespace Agca\Domain;

final class MotDePasse
{
    public static function verifier(string $clair, ?string $sha1, ?string $bcrypt): bool
    {
        if ($clair === '') { return false; }
        if ($bcrypt !== null && $bcrypt !== '') { return password_verify($clair, $bcrypt); }
        if ($sha1 !== null && $sha1 !== '') { return hash_equals(strtolower($sha1), sha1($clair)); }
        return false;
    }

    public static function hacher(string $clair): string
    {
        return password_hash($clair, PASSWORD_BCRYPT, ['cost' => 11]);
    }

    public static function doitMigrer(?string $sha1, ?string $bcrypt): bool
    {
        return ($bcrypt === null || $bcrypt === '') && $sha1 !== null && $sha1 !== '';
    }

    /** Mot de passe temporaire lisible, sans caractères ambigus. */
    public static function generer(): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        $out = '';
        for ($i = 0; $i < 12; $i++) { $out .= $alphabet[random_int(0, strlen($alphabet) - 1)]; }
        return $out;
    }
}
