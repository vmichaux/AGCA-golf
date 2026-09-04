<?php
declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';

use Agca\App\App;
use Agca\App\Config;
use Agca\App\Http\Request;

date_default_timezone_set('Europe/Paris');
mb_internal_encoding('UTF-8');

// Serveur de développement : servir directement les fichiers statiques existants.
if (PHP_SAPI === 'cli-server') {
    $f = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($f) && !str_ends_with($f, '.php')) { return false; }
}

$app = new App(Config::charger());
$req = Request::depuisGlobales();
$app->executer($req)->envoyer($req->estHead());
