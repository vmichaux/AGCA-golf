<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;

final class PublicController extends Controller
{
    public function accueil(Request $req): Response
    {
        return $this->rendre('public/accueil', ['titre' => 'AGCA — Interclubs']);
    }
}
