<?php
declare(strict_types=1);
namespace Agca\App;

use Agca\App\Controller\ContenuController;
use Agca\App\Http\Router;

/** Routes d'administration du contenu du site (chantier Contenu). */
final class RoutesContenu
{
    public static function declarer(Router $r): void
    {
        $r->get('/admin/contenu', [ContenuController::class, 'index']);
        $r->get('/admin/contenu/pages', [ContenuController::class, 'pages']);
        $r->get('/admin/contenu/pages/nouvelle', [ContenuController::class, 'nouvellePage']);
        $r->post('/admin/contenu/pages', [ContenuController::class, 'enregistrerPage']);
        $r->get('/admin/contenu/pages/{id}', [ContenuController::class, 'page']);
        $r->post('/admin/contenu/pages/{id}', [ContenuController::class, 'enregistrerPage']);
        $r->post('/admin/contenu/pages/{id}/supprimer', [ContenuController::class, 'supprimerPage']);
        $r->get('/admin/contenu/actualites', [ContenuController::class, 'actualites']);
        $r->get('/admin/contenu/actualites/nouvelle', [ContenuController::class, 'nouvelleActualite']);
        $r->post('/admin/contenu/actualites', [ContenuController::class, 'enregistrerActualite']);
        $r->get('/admin/contenu/actualites/{id}', [ContenuController::class, 'actualite']);
        $r->post('/admin/contenu/actualites/{id}', [ContenuController::class, 'enregistrerActualite']);
        $r->post('/admin/contenu/actualites/{id}/supprimer', [ContenuController::class, 'supprimerActualite']);
        $r->get('/admin/contenu/competitions', [ContenuController::class, 'competitions']);
        $r->get('/admin/contenu/competitions/{id}', [ContenuController::class, 'competition']);
        $r->post('/admin/contenu/competitions/{id}', [ContenuController::class, 'enregistrerCompetition']);
        $r->post('/admin/contenu/competitions/{id}/palmares', [ContenuController::class, 'ajouterPalmares']);
        $r->post('/admin/contenu/palmares/{id}', [ContenuController::class, 'modifierPalmares']);
        $r->post('/admin/contenu/palmares/{id}/supprimer', [ContenuController::class, 'supprimerPalmares']);
        $r->get('/admin/contenu/golfs', [ContenuController::class, 'golfs']);
        $r->post('/admin/contenu/golfs', [ContenuController::class, 'enregistrerGolf']);
        $r->post('/admin/contenu/golfs/{id}', [ContenuController::class, 'enregistrerGolf']);
        $r->get('/admin/contenu/organigramme', [ContenuController::class, 'organigramme']);
        $r->post('/admin/contenu/organigramme', [ContenuController::class, 'enregistrerOrganigramme']);
        $r->post('/admin/contenu/organigramme/{id}', [ContenuController::class, 'enregistrerOrganigramme']);
        $r->post('/admin/contenu/organigramme/{id}/supprimer', [ContenuController::class, 'supprimerOrganigramme']);
        $r->get('/admin/contenu/albums', [ContenuController::class, 'albums']);
        $r->post('/admin/contenu/albums', [ContenuController::class, 'enregistrerAlbum']);
        $r->post('/admin/contenu/albums/{id}', [ContenuController::class, 'enregistrerAlbum']);
        $r->post('/admin/contenu/albums/{id}/supprimer', [ContenuController::class, 'supprimerAlbum']);
        $r->get('/admin/contenu/documents', [ContenuController::class, 'documents']);
        $r->post('/admin/contenu/documents', [ContenuController::class, 'televerserDocument']);
        $r->post('/admin/contenu/documents/{id}/supprimer', [ContenuController::class, 'supprimerDocument']);
    }
}
