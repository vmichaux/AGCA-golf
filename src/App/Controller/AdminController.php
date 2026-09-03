<?php
declare(strict_types=1);
namespace Agca\App\Controller;

use Agca\App\Http\Request;
use Agca\App\Http\Response;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JoueurRepository;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;
use Agca\App\Service\Forfait;
use Agca\App\Service\Saisons;
use Agca\Domain\MotDePasse;

final class AdminController extends Controller
{
    public function index(Request $req): Response
    {
        $this->exigerAdmin();
        $saison = $this->app->service(Saisons::class)->courante(null);
        $alertes = $saison ? $this->app->service(RencontreRepository::class)->avecAlertes((int) $saison['id']) : [];
        $enRetard = $this->app->service(RencontreRepository::class)->aRelancer(date('Y-m-d', strtotime('-2 days')), 0);
        return $this->rendre('admin/index', ['titre' => 'Administration', 'saison' => $saison, 'nbAlertes' => count($alertes), 'enRetard' => $enRetard,
            'journal' => $this->app->service(JournalRepository::class)->recents(30), 'series' => $this->app->service(SerieRepository::class)->toutes()]);
    }

    public function alertes(Request $req): Response
    {
        $this->exigerAdmin();
        $saison = $this->app->service(Saisons::class)->courante(null);
        return $this->rendre('admin/alertes', ['titre' => 'Feuilles à vérifier', 'rencontres' => $saison ? $this->app->service(RencontreRepository::class)->avecAlertes((int) $saison['id']) : []]);
    }

    public function alertesVues(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $this->app->service(RencontreRepository::class)->marquerAlertesVues((int) $id);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'alertes_vues', 'rencontre', (int) $id);
        return $this->rediriger('/admin/alertes');
    }

    public function forfait(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $this->app->service(Forfait::class)->declarer((int) $id, (string) $req->post('camp', ''), $u);
        $this->flash('succes', 'Forfait enregistré et notifié.');
        return $this->rediriger('/rencontre/' . (int) $id);
    }

    public function annulerForfait(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $this->app->service(Forfait::class)->annuler((int) $id, $u);
        $this->flash('succes', 'Forfait annulé ; la rencontre est de nouveau à jouer.');
        return $this->rediriger('/rencontre/' . (int) $id);
    }

    public function utilisateurs(Request $req): Response
    {
        $this->exigerAdmin();
        $series = $this->app->service(SerieRepository::class)->toutes();
        $equipes = [];
        foreach ($series as $s) { $equipes[$s['id']] = $this->app->service(EquipeRepository::class)->parSerie((int) $s['id'], false); }
        return $this->rendre('admin/utilisateurs', ['titre' => 'Identifiants', 'utilisateurs' => $this->app->service(UtilisateurRepository::class)->tous(), 'series' => $series, 'equipes' => $equipes]);
    }

    public function creerUtilisateur(Request $req): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $identifiant = trim((string) $req->post('identifiant', ''));
        $serieId = (int) $req->post('serie_id', 0) ?: null;
        if ($identifiant === '') { $this->flash('erreur', 'Identifiant vide.'); return $this->rediriger('/admin/utilisateurs'); }
        $repo = $this->app->service(UtilisateurRepository::class);
        if ($repo->parIdentifiantEtSerie($identifiant, $serieId) !== null && $serieId !== null) { $this->flash('erreur', 'Cet identifiant existe déjà pour cette série.'); return $this->rediriger('/admin/utilisateurs'); }
        $mdp = MotDePasse::generer();
        $id = $repo->creer(['identifiant' => $identifiant, 'serie_id' => $serieId, 'equipe_id' => (int) $req->post('equipe_id', 0) ?: null, 'hash_bcrypt' => MotDePasse::hacher($mdp)]);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'utilisateur_cree', 'utilisateur', $id);
        $this->flash('succes', "Identifiant $identifiant créé. Mot de passe temporaire à transmettre : $mdp");
        return $this->rediriger('/admin/utilisateurs');
    }

    public function reinitialiser(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $cible = $this->app->service(UtilisateurRepository::class)->parId((int) $id) ?? $this->introuvable();
        $mdp = MotDePasse::generer();
        $this->app->service(UtilisateurRepository::class)->definirBcrypt((int) $id, MotDePasse::hacher($mdp));
        $this->app->service(UtilisateurRepository::class)->enregistrerSucces((int) $id);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'mot_de_passe_reinitialise', 'utilisateur', (int) $id);
        $this->flash('succes', "Nouveau mot de passe de {$cible['identifiant']} ({$cible['serie_code']}) : $mdp — à transmettre au capitaine, il n'est affiché qu'une fois.");
        return $this->rediriger('/admin/utilisateurs');
    }

    public function basculerAdmin(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        if ((int) $id === (int) $u['id'] && (string) $req->post('valeur') === '0') { $this->flash('erreur', 'Vous ne pouvez pas retirer votre propre droit admin.'); return $this->rediriger('/admin/utilisateurs'); }
        $this->app->service(UtilisateurRepository::class)->definirAdmin((int) $id, (string) $req->post('valeur') === '1');
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'admin_modifie', 'utilisateur', (int) $id, ['valeur' => $req->post('valeur')]);
        return $this->rediriger('/admin/utilisateurs');
    }

    public function rattacher(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $equipeId = (int) $req->post('equipe_id', 0) ?: null;
        $equipe = $equipeId ? $this->app->service(EquipeRepository::class)->parId($equipeId) : null;
        $this->app->service(UtilisateurRepository::class)->rattacher((int) $id, $equipeId, $equipe ? (int) $equipe['serie_id'] : null);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'utilisateur_rattache', 'utilisateur', (int) $id, ['equipe_id' => $equipeId]);
        return $this->rediriger('/admin/utilisateurs');
    }

    public function joueurs(Request $req): Response
    {
        $this->exigerAdmin();
        $golfId = (int) $req->get('golf', 0);
        return $this->rendre('admin/joueurs', ['titre' => 'Joueurs', 'golfs' => $this->app->service(GolfRepository::class)->tous(), 'golfId' => $golfId,
            'joueurs' => $golfId ? $this->app->service(JoueurRepository::class)->parGolf($golfId) : []]);
    }

    public function modifierJoueur(Request $req, string $id): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $j = $this->app->service(JoueurRepository::class)->parId((int) $id) ?? $this->introuvable();
        $sexe = strtoupper((string) $req->post('sexe', ''));
        $this->app->service(JoueurRepository::class)->modifier((int) $id, (string) $req->post('nom', $j['nom']), trim((string) $req->post('prenom', '')) ?: null, in_array($sexe, ['H', 'D'], true) ? $sexe : null);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'joueur_modifie', 'joueur', (int) $id);
        return $this->rediriger('/admin/joueurs?golf=' . (int) $j['golf_id']);
    }

    public function fusionnerJoueurs(Request $req): Response
    {
        $u = $this->exigerAdmin(); $this->exigerCsrf($req);
        $src = (int) $req->post('source_id', 0); $cible = (int) $req->post('cible_id', 0);
        $repo = $this->app->service(JoueurRepository::class);
        $js = $repo->parId($src); $jc = $repo->parId($cible);
        if ($js === null || $jc === null || $src === $cible || $js['golf_id'] !== $jc['golf_id']) { $this->flash('erreur', 'Fusion impossible : choisir deux joueurs différents du même golf.'); return $this->rediriger('/admin/joueurs?golf=' . (int) ($js['golf_id'] ?? 0)); }
        $n = $repo->fusionner($src, $cible);
        $this->app->service(JournalRepository::class)->ecrire((int) $u['id'], 'joueurs_fusionnes', 'joueur', $cible, ['source' => $src, 'parties' => $n]);
        $this->flash('succes', "{$js['nom']} fusionné dans {$jc['nom']} ($n partie(s) réaffectée(s)).");
        return $this->rediriger('/admin/joueurs?golf=' . (int) $jc['golf_id']);
    }
}
