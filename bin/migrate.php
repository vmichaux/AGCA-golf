#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../vendor/autoload.php';

use Agca\App\Config;
use Agca\App\Db;
use Agca\App\Migrations;

$cfg = Config::charger($argv[1] ?? null);
$db = Db::depuisConfig($cfg);
$faites = Migrations::appliquer($db, __DIR__ . '/../db/migrations');
echo $faites === [] ? "Rien à appliquer.\n" : 'Appliqué : ' . implode(', ', $faites) . "\n";
