#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../vendor/autoload.php';

use Agca\App\App;
use Agca\App\Config;
use Agca\App\Service\ContenuInitial;

$app = new App(Config::charger($argv[1] ?? null));
$r = $app->service(ContenuInitial::class)->executer(null);
foreach ($r['rapport'] as $l) { echo "  $l\n"; }
exit(0);
