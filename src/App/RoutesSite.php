<?php
declare(strict_types=1);
namespace Agca\App;

use Agca\App\Controller\SiteController;
use Agca\App\Http\Router;

/** Routes publiques du site (chantier Design). */
final class RoutesSite
{
    public static function declarer(Router $r): void
    {
        $r->get('/competitions', [SiteController::class, 'competitions']);
        $r->get('/competitions/{code}', [SiteController::class, 'competition']);
        $r->get('/golfs', [SiteController::class, 'golfs']);
        $r->get('/photos', [SiteController::class, 'photos']);
        $r->get('/actualites', [SiteController::class, 'actualites']);
        $r->get('/actualites/{ref}', [SiteController::class, 'actualite']);
        $r->get('/association/organigramme', [SiteController::class, 'organigramme']);
        $r->get('/page/{slug}', [SiteController::class, 'page']);
        $r->get('/mentions-legales', [SiteController::class, 'mentionsLegales']);
        $r->get('/contact', [SiteController::class, 'contact']);
        $r->post('/contact', [SiteController::class, 'envoyerContact']);
    }
}
