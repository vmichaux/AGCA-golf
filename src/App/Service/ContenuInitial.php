<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\AlbumRepository;
use Agca\App\Repository\CompetitionRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\PageRepository;
use Agca\Domain\Markdown;

final class ContenuInitial
{
    public function __construct(private App $app) {}

    /** @return array{rapport: list<string>} */
    public function executer(?int $utilisateurId, ?array $donnees = null): array
    {
        $d = $donnees ?? require $this->app->racine() . '/db/contenu/initial.php';
        $rapport = [];
        $pages = $this->app->service(PageRepository::class);
        foreach ($d['pages'] as $p) {
            if ($pages->parSlug($p['slug']) !== null) { continue; }
            $pages->creer(['slug' => $p['slug'], 'titre' => $p['titre'], 'corps_md' => $p['corps_md'], 'corps_html' => Markdown::rendre($p['corps_md']), 'systeme' => $p['systeme'] ?? 1, 'dans_menu' => $p['dans_menu'] ?? 0, 'ordre' => $p['ordre'] ?? 0, 'modifie_par' => $utilisateurId]);
            $rapport[] = "Page {$p['slug']} créée.";
        }
        $comps = $this->app->service(CompetitionRepository::class);
        foreach ($d['competitions'] as $c) {
            if ($comps->parCode($c['code']) !== null) { continue; }
            $id = $comps->creer(['code' => $c['code'], 'nom' => $c['nom'], 'accroche' => $c['accroche'], 'formule' => $c['formule'], 'corps_md' => $c['corps_md'], 'corps_html' => Markdown::rendre($c['corps_md']), 'ordre' => $c['ordre'], 'actif' => 1]);
            foreach ($c['palmares'] as $p) {
                $comps->ajouterPalmares(['competition_id' => $id, 'saison' => $p['saison'], 'lieu' => $p['lieu'], 'vainqueur' => $p['vainqueur'], 'detail_md' => $p['detail_md'], 'detail_html' => $p['detail_md'] === null ? null : Markdown::rendre($p['detail_md']), 'document_id' => null, 'ordre' => 0]);
            }
            $rapport[] = "Compétition {$c['code']} créée avec " . count($c['palmares']) . ' palmarès.';
        }
        $golfs = $this->app->service(GolfRepository::class);
        foreach ($d['golfs'] as $g) {
            $existant = $golfs->parNom($g['nom']);
            $id = $existant === null ? $golfs->trouverOuCreer($g['nom']) : (int) $existant['id'];
            // membre = 1 seulement à la création : un golf que l'admin a retiré des membres ne doit pas y revenir.
            $champs = $existant === null ? ['membre' => 1] : [];
            if (empty($existant['ville']) && !empty($g['ville'])) { $champs['ville'] = $g['ville']; }
            if (empty($existant['site_web']) && !empty($g['site_web'])) { $champs['site_web'] = preg_match('#^https?://#', $g['site_web']) ? $g['site_web'] : 'https://' . $g['site_web']; }
            if ($champs !== []) { $golfs->modifier($id, $champs); }
            $rapport[] = ($existant === null ? 'Golf créé : ' : 'Golf complété : ') . $g['nom'];
        }
        $albums = $this->app->service(AlbumRepository::class);
        $urls = array_column($albums->tous(), 'url');
        foreach ($d['albums'] as $a) {
            if (in_array($a['url'], $urls, true)) { continue; }
            $albums->creer(['titre' => $a['titre'], 'annee' => $a['annee'], 'url' => $a['url'], 'ordre' => 0]);
            $rapport[] = "Album {$a['titre']} créé.";
        }
        $this->app->service(JournalRepository::class)->ecrire($utilisateurId, 'contenu_initial', null, null, ['lignes' => count($rapport)]);
        return ['rapport' => $rapport];
    }
}
