<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Service\Accueil;
use Agca\Domain\Markdown;

/** Pages publiques du site (accueil, compétitions, golfs, actualités, association, contact). */
final class SiteController extends Controller
{
    private const ACCROCHE = 'Le golf par équipes entre clubs amis, de septembre à mai, dans l\'esprit de Saint Andrews et toujours autour d\'une bonne table.';

    public function accueil(Request $req): Response
    {
        $donnees = $this->app->service(Accueil::class)->donnees(new \DateTimeImmutable('today'));
        $accroche = $donnees['pages']['accueil-accroche'];
        $description = $accroche === null || trim((string) $accroche['corps_md']) === ''
            ? self::ACCROCHE
            : Markdown::texteBrut((string) $accroche['corps_md'], 200);
        return $this->rendre('site/accueil', $donnees + [
            'titre' => 'AGCA — Interclubs de golf en PACA',
            'description' => $description,
            'accroche_repli' => self::ACCROCHE,
        ]);
    }
}
