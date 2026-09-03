<?php
declare(strict_types=1);
namespace Agca\App;

final class Config
{
    /** @return array<string, mixed> */
    public static function charger(?string $chemin = null): array
    {
        $chemin ??= getenv('AGCA_CONFIG') ?: dirname(__DIR__, 2) . '/config/config.php';
        if (!is_file($chemin)) {
            throw new \RuntimeException("Fichier de configuration introuvable : $chemin (copier config/config.php.dist)");
        }
        $cfg = require $chemin;
        if (!is_array($cfg)) { throw new \RuntimeException('config.php doit retourner un tableau'); }
        return $cfg;
    }
}
