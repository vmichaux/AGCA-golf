#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../vendor/autoload.php';

use Agca\App\App;
use Agca\App\Config;
use Agca\App\Service\Relances;

date_default_timezone_set('Europe/Paris');
try {
    $app = new App(Config::charger($argv[1] ?? null));
    $r = $app->service(Relances::class)->executer(new DateTimeImmutable());
    echo date('Y-m-d H:i') . ' : ' . $r['envoyees'] . " relance(s) envoyée(s)" . ($r['rencontres'] ? ' (rencontres ' . implode(', ', $r['rencontres']) . ')' : '') . "\n";
} catch (\Throwable $e) {
    fwrite(STDERR, date('Y-m-d H:i') . " : ERREUR relances : " . $e->getMessage() . "\n");
    exit(1);
}
