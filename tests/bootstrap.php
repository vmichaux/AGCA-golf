<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('Europe/Paris');

// Base de test propre à un worktree : fichier .agca-test-db (non versionné) contenant le nom de la base.
$fichierBase = __DIR__ . '/../.agca-test-db';
if (is_file($fichierBase)) {
    $base = trim((string) file_get_contents($fichierBase));
    if ($base !== '') { putenv('AGCA_TEST_DSN=mysql:host=127.0.0.1;dbname=' . $base . ';charset=utf8mb4'); }
}
