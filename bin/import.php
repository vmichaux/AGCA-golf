#!/usr/bin/env php
<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Agca\App\App;
use Agca\App\Config;
use Agca\App\Service\ImportInitial;

$app = new App(Config::charger($argv[1] ?? null));
$donnees = require __DIR__ . '/../db/import/saison_2026_27.php';
$r = $app->service(ImportInitial::class)->executer($donnees, ['id' => null, 'identifiant' => 'import', 'est_admin' => 1]);
foreach ($r['rapport'] as $l) { echo "  $l\n"; }
foreach ($r['erreurs'] as $l) { fwrite(STDERR, "ERREUR : $l\n"); }
exit($r['erreurs'] === [] ? 0 : 1);
