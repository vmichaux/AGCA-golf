<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\ActualiteRepository;
use Agca\App\Repository\AlbumRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\OrganigrammeRepository;
use Agca\App\Repository\PageRepository;
use Agca\App\Service\Accueil;
use Agca\App\Service\Contact;
use Agca\Domain\Markdown;

/** Pages publiques du site (accueil, compétitions, golfs, actualités, association, contact). */
final class SiteController extends Controller
{
    private const ACCROCHE = 'Le golf par équipes entre clubs amis, de septembre à mai, dans l\'esprit de Saint Andrews et toujours autour d\'une bonne table.';
    private const ACTUALITES_PAR_PAGE = 10;
    /** Fragments éditoriaux de l'accueil : ce sont des morceaux de page, pas des pages. */
    private const FRAGMENTS = '/^(?:accueil-|format-)|^citation$/';

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

    public function competitions(Request $req): Response
    {
        $repo = $this->app->service(CompetitionRepository::class);
        $competitions = [];
        foreach ($repo->toutes() as $c) {
            $c['dernier_palmares'] = $repo->dernierPalmares((int) $c['id']);
            $competitions[] = $c;
        }
        return $this->rendre('site/competitions', ['titre' => 'Compétitions individuelles — AGCA', 'competitions' => $competitions]);
    }

    public function competition(Request $req, string $code): Response
    {
        $repo = $this->app->service(CompetitionRepository::class);
        $competition = $repo->parCode($code);
        if ($competition === null || (int) $competition['actif'] !== 1) { $this->introuvable('Compétition introuvable'); }
        return $this->rendre('site/competition', [
            'titre' => $competition['nom'] . ' — AGCA',
            'description' => (string) ($competition['formule'] ?? ''),
            'competition' => $competition,
            'competitions' => $repo->toutes(),
            'palmares' => $repo->palmares((int) $competition['id']),
        ]);
    }

    public function golfs(Request $req): Response
    {
        return $this->rendre('site/golfs', ['titre' => 'Golfs membres — AGCA', 'golfs' => $this->app->service(GolfRepository::class)->membres()]);
    }

    public function photos(Request $req): Response
    {
        return $this->rendre('site/photos', ['titre' => 'Photos — AGCA', 'albums' => $this->app->service(AlbumRepository::class)->tous()]);
    }

    public function actualites(Request $req): Response
    {
        $repo = $this->app->service(ActualiteRepository::class);
        $total = $repo->compterPubliees();
        $nbPages = max(1, (int) ceil($total / self::ACTUALITES_PAR_PAGE));
        $page = max(1, min($nbPages, (int) $req->get('page', 1)));
        return $this->rendre('site/actualites', [
            'titre' => 'Actualités — AGCA',
            'actualites' => $repo->publiees(self::ACTUALITES_PAR_PAGE, ($page - 1) * self::ACTUALITES_PAR_PAGE),
            'page' => $page,
            'nb_pages' => $nbPages,
        ]);
    }

    /** `$ref` vaut « 12-la-saison-demarre » : l'identifiant fait foi, le slug est décoratif. */
    public function actualite(Request $req, string $ref): Response
    {
        if (preg_match('/^(\d+)(?:-|$)/', $ref, $m) !== 1) { $this->introuvable('Actualité introuvable'); }
        $id = (int) $m[1];
        $actualite = $this->app->service(ActualiteRepository::class)->parId($id);
        if ($actualite === null || (int) $actualite['publie'] !== 1) { $this->introuvable('Actualité introuvable'); }
        $canonique = $id . '-' . $actualite['slug'];
        if ($ref !== $canonique) { return new Response(301, '', ['Location' => '/actualites/' . $canonique]); }
        return $this->rendre('site/actualite', [
            'titre' => $actualite['titre'] . ' — AGCA',
            'description' => Markdown::texteBrut((string) ($actualite['resume'] ?? '') ?: (string) $actualite['corps_md'], 200),
            'actualite' => $actualite,
        ]);
    }

    public function organigramme(Request $req): Response
    {
        return $this->rendre('site/organigramme', [
            'titre' => 'Organigramme — AGCA',
            'groupes' => $this->app->service(OrganigrammeRepository::class)->parGroupe(),
        ]);
    }

    public function page(Request $req, string $slug): Response
    {
        if (preg_match(self::FRAGMENTS, $slug) === 1) { $this->introuvable(); }
        return $this->rendrePage($slug);
    }

    public function mentionsLegales(Request $req): Response
    {
        return $this->rendrePage('mentions-legales');
    }

    private function rendrePage(string $slug): Response
    {
        $repo = $this->app->service(PageRepository::class);
        $page = $repo->parSlug($slug);
        if ($page === null) { $this->introuvable(); }
        return $this->rendre('site/page', [
            'titre' => $page['titre'] . ' — AGCA',
            'description' => Markdown::texteBrut((string) $page['corps_md'], 200),
            'page' => $page,
            'documents' => $repo->documents((int) $page['id']),
        ]);
    }

    public function contact(Request $req): Response
    {
        return $this->rendreContact([]);
    }

    public function envoyerContact(Request $req): Response
    {
        $this->exigerCsrf($req);
        $r = $this->app->service(Contact::class)->envoyer($req->tousPost(), $this->app->session(), $req->ip());
        if (!$r['ok']) { return $this->rendreContact($req->tousPost(), $r['erreurs']); }
        $this->flash('succes', 'Votre message a bien été envoyé.');
        return $this->rediriger('/contact');
    }

    /** @param array<string, mixed> $post @param list<string> $erreurs */
    private function rendreContact(array $post, array $erreurs = []): Response
    {
        $valeurs = [];
        foreach (['nom', 'email', 'objet', 'message'] as $champ) { $valeurs[$champ] = trim((string) ($post[$champ] ?? '')); }
        return $this->rendre('site/contact', ['titre' => 'Contact — AGCA', 'valeurs' => $valeurs, 'erreurs' => $erreurs],
            $erreurs === [] ? 200 : 422);
    }
}
