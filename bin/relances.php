#!/usr/bin/env php
<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Agca\App\App;
use Agca\App\Config;
use Agca\App\Service\Relances;

date_default_timezone_set('Europe/Paris');
$app = new App(Config::charger($argv[1] ?? null));
$r = $app->service(Relances::class)->executer(new DateTimeImmutable());
echo date('Y-m-d H:i') . ' : ' . $r['envoyees'] . " relance(s) envoyée(s)" . ($r['rencontres'] ? ' (rencontres ' . implode(', ', $r['rencontres']) . ')' : '') . "\n";
