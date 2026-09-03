<?php
declare(strict_types=1);
namespace Agca\App;

use Agca\App\Controller\PublicController;
use Agca\App\Http\Router;

final class Routes
{
    public static function declarer(Router $r): void
    {
        $r->get('/', [PublicController::class, 'accueil']);
    }
}
