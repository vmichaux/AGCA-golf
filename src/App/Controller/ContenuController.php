<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\ActualiteRepository;
use Agca\App\Repository\AlbumRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\DocumentRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\OrganigrammeRepository;
use Agca\App\Repository\PageRepository;
use Agca\App\Service\Documents;
use Agca\Domain\Markdown;

final class ContenuController extends Controller
{
    // ----- tableau de bord -----
    public function index(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/index', ['titre' => 'Contenu du site',
            'nbPages' => count($this->app->service(PageRepository::class)->toutes()),
            'nbActualites' => count($this->app->service(ActualiteRepository::class)->toutes()),
            'nbCompetitions' => count($this->app->service(CompetitionRepository::class)->toutes(false)),
            'nbGolfs' => count($this->app->service(GolfRepository::class)->membres()),
            'nbDocuments' => count($this->app->service(DocumentRepository::class)->tous())]);
    }

    // ----- pages -----
    public function pages(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/pages', ['titre' => 'Pages', 'pages' => $this->app->service(PageRepository::class)->toutes()]);
    }

    public function nouvellePage(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/page', ['titre' => 'Nouvelle page', 'page' => ['id' => null, 'slug' => '', 'titre' => '', 'corps_md' => '', 'systeme' => 0, 'dans_menu' => 0, 'ordre' => 0], 'documents' => $this->app->service(DocumentRepository::class)->tous(), 'joints' => [], 'erreurs' => []]);
    }

    public function page(Request $req, string $id): Response
    {
        $this->exigerAdmin();
        $repo = $this->app->service(PageRepository::class);
        $page = $repo->parId((int) $id) ?? $this->introuvable();
        return $this->rendre('admin/contenu/page', ['titre' => 'Page : ' . $page['titre'], 'page' => $page, 'documents' => $this->app->service(DocumentRepository::class)->tous(),
            'joints' => array_map('intval', array_column($repo->documents((int) $id), 'id')), 'erreurs' => []]);
    }

    public function enregistrerPage(Request $req, ?string $id = null): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $repo = $this->app->service(PageRepository::class);
        $existante = $id !== null ? ($repo->parId((int) $id) ?? $this->introuvable()) : null;
        $slug = strtolower(trim((string) $req->post('slug', '')));
        $titre = trim((string) $req->post('titre', ''));
        $md = (string) $req->post('corps_md', '');
        $erreurs = [];
        if ($titre === '') { $erreurs[] = 'Le titre est obligatoire.'; }
        if ($existante === null || (int) $existante['systeme'] === 0) {
            if (!preg_match('/^[a-z0-9-]{2,80}$/', $slug)) { $erreurs[] = 'Adresse (slug) invalide : lettres minuscules, chiffres et tirets.'; }
            $doublon = $repo->parSlug($slug);
            if ($doublon !== null && ($existante === null || (int) $doublon['id'] !== (int) $existante['id'])) { $erreurs[] = 'Cette adresse est déjà utilisée.'; }
        } else { $slug = $existante['slug']; }
        $champs = ['slug' => $slug, 'titre' => $titre, 'corps_md' => $md, 'corps_html' => Markdown::rendre($md), 'dans_menu' => $req->post('dans_menu') ? 1 : 0, 'ordre' => (int) $req->post('ordre', 0), 'modifie_par' => (int) $u['id']];
        $joints = array_map('intval', (array) $req->post('documents', []));
        if ($erreurs !== []) {
            return $this->rendre('admin/contenu/page', ['titre' => $existante ? 'Page : ' . $existante['titre'] : 'Nouvelle page', 'page' => ($existante ?? ['id' => null, 'systeme' => 0]) + $champs,
                'documents' => $this->app->service(DocumentRepository::class)->tous(), 'joints' => $joints, 'erreurs' => $erreurs], 422);
        }
        if ($existante === null) { $pageId = $repo->creer($champs + ['systeme' => 0]); $action = 'page_creee'; }
        else { $pageId = (int) $existante['id']; $repo->modifier($pageId, $champs); $action = 'page_modifiee'; }
        $repo->definirDocuments($pageId, $joints);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], $action, 'page', $pageId, ['slug' => $slug]);
        $this->flash('succes', 'Page enregistrée.');
        return $this->rediriger('/admin/contenu/pages/' . $pageId);
    }

    public function supprimerPage(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $ok = $this->app->service(PageRepository::class)->supprimer((int) $id);
        $this->flash($ok ? 'succes' : 'erreur', $ok ? 'Page supprimée.' : 'Cette page est requise par le site et ne peut pas être supprimée.');
        if ($ok) { $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'page_supprimee', 'page', (int) $id); }
        return $this->rediriger('/admin/contenu/pages');
    }

    // ----- actualités -----
    public function actualites(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/actualites', ['titre' => 'Actualités', 'actualites' => $this->app->service(ActualiteRepository::class)->toutes()]);
    }

    public function nouvelleActualite(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/actualite', ['titre' => 'Nouvelle actualité', 'actualite' => ['id' => null, 'titre' => '', 'date_publication' => date('Y-m-d'), 'resume' => '', 'corps_md' => '', 'publie' => 0], 'erreurs' => []]);
    }

    public function actualite(Request $req, string $id): Response
    {
        $this->exigerAdmin();
        $a = $this->app->service(ActualiteRepository::class)->parId((int) $id) ?? $this->introuvable();
        return $this->rendre('admin/contenu/actualite', ['titre' => 'Actualité : ' . $a['titre'], 'actualite' => $a, 'erreurs' => []]);
    }

    public function enregistrerActualite(Request $req, ?string $id = null): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $repo = $this->app->service(ActualiteRepository::class);
        $existante = $id !== null ? ($repo->parId((int) $id) ?? $this->introuvable()) : null;
        $titre = trim((string) $req->post('titre', '')); $date = (string) $req->post('date_publication', ''); $md = (string) $req->post('corps_md', '');
        $erreurs = [];
        if ($titre === '') { $erreurs[] = 'Le titre est obligatoire.'; }
        if (\DateTimeImmutable::createFromFormat('!Y-m-d', $date) === false) { $erreurs[] = 'Date de publication invalide.'; }
        $champs = ['titre' => $titre, 'slug' => self::slug($titre), 'date_publication' => $date, 'resume' => trim((string) $req->post('resume', '')) ?: null,
            'corps_md' => $md, 'corps_html' => Markdown::rendre($md), 'publie' => $req->post('publie') ? 1 : 0, 'modifie_par' => (int) $u['id']];
        if ($erreurs !== []) {
            return $this->rendre('admin/contenu/actualite', ['titre' => $existante ? 'Actualité' : 'Nouvelle actualité', 'actualite' => ($existante ?? ['id' => null]) + $champs, 'erreurs' => $erreurs], 422);
        }
        if ($existante === null) { $aid = $repo->creer($champs); $action = 'actualite_creee'; }
        else { $aid = (int) $existante['id']; $repo->modifier($aid, $champs); $action = 'actualite_modifiee'; }
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], $action, 'actualite', $aid, ['titre' => $titre]);
        $this->flash('succes', 'Actualité enregistrée.');
        return $this->rediriger('/admin/contenu/actualites/' . $aid);
    }

    public function supprimerActualite(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $this->app->service(ActualiteRepository::class)->supprimer((int) $id);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'actualite_supprimee', 'actualite', (int) $id);
        $this->flash('succes', 'Actualité supprimée.');
        return $this->rediriger('/admin/contenu/actualites');
    }

    // ----- compétitions et palmarès -----
    public function competitions(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/competitions', ['titre' => 'Compétitions', 'competitions' => $this->app->service(CompetitionRepository::class)->toutes(false)]);
    }

    public function competition(Request $req, string $id): Response
    {
        $this->exigerAdmin();
        $c = $this->app->service(CompetitionRepository::class);
        $comp = $c->parId((int) $id) ?? $this->introuvable();
        return $this->rendre('admin/contenu/competition', ['titre' => $comp['nom'], 'competition' => $comp, 'palmares' => $c->palmares((int) $id), 'documents' => $this->app->service(DocumentRepository::class)->parCategorie('palmares')]);
    }

    public function enregistrerCompetition(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $c = $this->app->service(CompetitionRepository::class);
        $c->parId((int) $id) ?? $this->introuvable();
        $md = (string) $req->post('corps_md', '');
        $c->modifier((int) $id, ['nom' => trim((string) $req->post('nom', '')) ?: 'Compétition', 'accroche' => trim((string) $req->post('accroche', '')) ?: null, 'formule' => trim((string) $req->post('formule', '')) ?: null,
            'corps_md' => $md, 'corps_html' => Markdown::rendre($md), 'ordre' => (int) $req->post('ordre', 0), 'actif' => $req->post('actif') ? 1 : 0]);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'competition_modifiee', 'competition', (int) $id);
        $this->flash('succes', 'Fiche enregistrée.');
        return $this->rediriger('/admin/contenu/competitions/' . (int) $id);
    }

    public function ajouterPalmares(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $c = $this->app->service(CompetitionRepository::class);
        $c->parId((int) $id) ?? $this->introuvable();
        $pid = $c->ajouterPalmares($this->champsPalmares($req) + ['competition_id' => (int) $id, 'ordre' => 0]);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'palmares_ajoute', 'palmares', $pid);
        $this->flash('succes', 'Palmarès ajouté.');
        return $this->rediriger('/admin/contenu/competitions/' . (int) $id);
    }

    public function modifierPalmares(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $c = $this->app->service(CompetitionRepository::class);
        $p = $c->palmaresParId((int) $id) ?? $this->introuvable();
        $c->modifierPalmares((int) $id, $this->champsPalmares($req));
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'palmares_modifie', 'palmares', (int) $id);
        $this->flash('succes', 'Palmarès modifié.');
        return $this->rediriger('/admin/contenu/competitions/' . (int) $p['competition_id']);
    }

    public function supprimerPalmares(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $c = $this->app->service(CompetitionRepository::class);
        $p = $c->palmaresParId((int) $id) ?? $this->introuvable();
        $c->supprimerPalmares((int) $id);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'palmares_supprime', 'palmares', (int) $id);
        return $this->rediriger('/admin/contenu/competitions/' . (int) $p['competition_id']);
    }

    private function champsPalmares(Request $req): array
    {
        $md = trim((string) $req->post('detail_md', ''));
        return ['saison' => trim((string) $req->post('saison', '')) ?: date('Y'), 'lieu' => trim((string) $req->post('lieu', '')) ?: null, 'vainqueur' => trim((string) $req->post('vainqueur', '')) ?: null,
            'detail_md' => $md ?: null, 'detail_html' => $md === '' ? null : Markdown::rendre($md), 'document_id' => (int) $req->post('document_id', 0) ?: null];
    }

    // ----- golfs -----
    public function golfs(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/golfs', ['titre' => 'Golfs', 'golfs' => $this->app->service(GolfRepository::class)->tous()]);
    }

    public function enregistrerGolf(Request $req, ?string $id = null): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $repo = $this->app->service(GolfRepository::class);
        $champs = ['nom' => mb_strtoupper(trim((string) $req->post('nom', ''))), 'ville' => trim((string) $req->post('ville', '')) ?: null, 'site_web' => trim((string) $req->post('site_web', '')) ?: null,
            'membre' => $req->post('membre') ? 1 : 0, 'ordre' => (int) $req->post('ordre', 0)];
        if ($champs['nom'] === '') { $this->flash('erreur', 'Le nom du golf est obligatoire.'); return $this->rediriger('/admin/contenu/golfs'); }
        if ($id === null) { $gid = $repo->trouverOuCreer($champs['nom']); $repo->modifier($gid, $champs); }
        else { $gid = (int) $id; $repo->parId($gid) ?? $this->introuvable(); $repo->modifier($gid, $champs); }
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'golf_modifie', 'golf', $gid, $champs);
        $this->flash('succes', 'Golf enregistré.');
        return $this->rediriger('/admin/contenu/golfs');
    }

    // ----- organigramme, albums (même patron) -----
    public function organigramme(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/organigramme', ['titre' => 'Organigramme', 'lignes' => $this->app->service(OrganigrammeRepository::class)->tous()]);
    }

    public function enregistrerOrganigramme(Request $req, ?string $id = null): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $repo = $this->app->service(OrganigrammeRepository::class);
        $champs = ['groupe' => in_array($req->post('groupe'), ['bureau', 'ca'], true) ? $req->post('groupe') : 'ca', 'fonction' => trim((string) $req->post('fonction', '')), 'prenom' => trim((string) $req->post('prenom', '')) ?: null,
            'nom' => trim((string) $req->post('nom', '')), 'golf' => trim((string) $req->post('golf', '')) ?: null, 'ordre' => (int) $req->post('ordre', 0)];
        if ($champs['nom'] === '' || $champs['fonction'] === '') { $this->flash('erreur', 'Nom et fonction sont obligatoires.'); return $this->rediriger('/admin/contenu/organigramme'); }
        if ($id === null) { $oid = $repo->creer($champs); } else { $oid = (int) $id; $repo->parId($oid) ?? $this->introuvable(); $repo->modifier($oid, $champs); }
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'organigramme_modifie', 'organigramme', $oid);
        return $this->rediriger('/admin/contenu/organigramme');
    }

    public function supprimerOrganigramme(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $this->app->service(OrganigrammeRepository::class)->supprimer((int) $id);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'organigramme_supprime', 'organigramme', (int) $id);
        return $this->rediriger('/admin/contenu/organigramme');
    }

    public function albums(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/albums', ['titre' => 'Albums photo', 'albums' => $this->app->service(AlbumRepository::class)->tous()]);
    }

    public function enregistrerAlbum(Request $req, ?string $id = null): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $repo = $this->app->service(AlbumRepository::class);
        $champs = ['titre' => trim((string) $req->post('titre', '')), 'annee' => (int) $req->post('annee', 0) ?: null, 'url' => trim((string) $req->post('url', '')), 'ordre' => (int) $req->post('ordre', 0)];
        if ($champs['titre'] === '' || $champs['url'] === '' || !preg_match('#^(/|https?://)#', $champs['url'])) { $this->flash('erreur', 'Titre et adresse (commençant par / ou https://) sont obligatoires.'); return $this->rediriger('/admin/contenu/albums'); }
        if ($id === null) { $aid = $repo->creer($champs); } else { $aid = (int) $id; $repo->parId($aid) ?? $this->introuvable(); $repo->modifier($aid, $champs); }
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'album_modifie', 'album', $aid);
        return $this->rediriger('/admin/contenu/albums');
    }

    public function supprimerAlbum(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $this->app->service(AlbumRepository::class)->supprimer((int) $id);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'album_supprime', 'album', (int) $id);
        return $this->rediriger('/admin/contenu/albums');
    }

    // ----- documents -----
    public function documents(Request $req): Response
    {
        $this->exigerAdmin();
        return $this->rendre('admin/contenu/documents', ['titre' => 'Documents', 'documents' => $this->app->service(DocumentRepository::class)->tous(), 'categories' => Documents::CATEGORIES]);
    }

    public function televerserDocument(Request $req): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $r = $this->app->service(Documents::class)->televerser($req->fichier('fichier'), (string) $req->post('titre', ''), (string) $req->post('categorie', 'autre'), (int) $u['id']);
        $this->flash($r['erreur'] === null ? 'succes' : 'erreur', $r['erreur'] ?? 'Document téléversé.');
        return $this->rediriger('/admin/contenu/documents');
    }

    public function supprimerDocument(Request $req, string $id): Response
    {
        $this->exigerAdmin(); $this->exigerCsrf($req);
        $erreur = $this->app->service(Documents::class)->supprimer((int) $id);
        $this->flash($erreur === null ? 'succes' : 'erreur', $erreur ?? 'Document supprimé.');
        return $this->rediriger('/admin/contenu/documents');
    }

    public static function slug(string $texte): string
    {
        // Translittération explicite des accents français : la table TRANSLIT d'iconv varie selon la
        // plate-forme (ex. macOS/libiconv rend « é » en « 'e » au lieu de « e »), ce qui casserait le slug.
        $accents = ['à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c',
            'œ' => 'oe', 'æ' => 'ae', 'ÿ' => 'y'];
        $t = strtr(mb_strtolower($texte, 'UTF-8'), $accents);
        $t = (string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($t)), '-') ?: 'actualite';
    }
}
