<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\JoueurRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\PartieRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;

final class RepositoriesTest extends DbTestCase
{
    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new App($this->config());
    }

    public function testCycleCompletSaisonDivisionRencontre(): void
    {
        $saisons = $this->app->service(SaisonRepository::class);
        $series = $this->app->service(SerieRepository::class);
        $golfs = $this->app->service(GolfRepository::class);
        $equipes = $this->app->service(EquipeRepository::class);
        $divisions = $this->app->service(DivisionRepository::class);
        $journees = $this->app->service(JourneeRepository::class);
        $rencontres = $this->app->service(RencontreRepository::class);

        $saisonId = $saisons->creer('2026-27', '2026-09-01', '2027-06-30');
        self::assertSame('2026-27', $saisons->active()['libelle']);
        $m = $series->parCode('M');
        self::assertSame(15, $m['serie']->nbParties());

        $salon = $golfs->trouverOuCreer('SALON');
        self::assertSame($salon, $golfs->trouverOuCreer('salon'));
        $e1 = $equipes->creer(['golf_id' => $salon, 'serie_id' => $m['id'], 'nom' => 'SALON', 'capitaine_nom' => 'TRAPY', 'capitaine_prenom' => 'Jean-Paul', 'capitaine_email' => 'jp@test', 'capitaine_tel' => '06']);
        $e2 = $equipes->creer(['golf_id' => $golfs->trouverOuCreer('FREGATE'), 'serie_id' => $m['id'], 'nom' => 'FREGATE']);
        self::assertSame('SALON', $equipes->parId($e1)['golf_nom']);
        self::assertSame($e1, $equipes->parNomEtSerie('SALON', $m['id'])['id']);

        $div = $divisions->creer($saisonId, $m['id'], 'DIV2/POULE B', 1);
        $divisions->ajouterEquipe($div, $e1, 1);
        $divisions->ajouterEquipe($div, $e2, 2);
        self::assertSame([1, 2], array_column($divisions->equipes($div), 'position'));
        self::assertSame('DIV2/POULE B', $divisions->divisionDeLEquipe($e2, $saisonId)['libelle']);

        $j1 = $journees->creer($saisonId, $m['id'], 1, 'aller', '2026-09-26');
        $r = $rencontres->creer($div, $j1, $e1, $e2, '2026-09-26');
        $ligne = $rencontres->parId($r);
        self::assertSame(['SALON', 'FREGATE', 'M', 'a_jouer', 'DIV2/POULE B', 'active'],
            [$ligne['recevant_nom'], $ligne['invite_nom'], $ligne['serie_code'], $ligne['statut'], $ligne['division_libelle'], $ligne['saison_statut']]);
        self::assertCount(1, $rencontres->parEquipeEtSaison($e2, $saisonId));

        $rencontres->mettreAJourDate($r, '2026-10-03', true);
        self::assertSame(['2026-10-03', 1], [$rencontres->parId($r)['date_reelle'], (int) $rencontres->parId($r)['reportee']]);

        $rencontres->mettreAJourResultat($r, ['statut' => 'enregistree', 'forfaitaire_id' => null, 'total_pour' => 20, 'total_contre' => 10,
            'pts_rencontre_pour' => 3, 'pts_rencontre_contre' => 1, 'bonus_invite' => 0, 'alertes' => ['FEUILLE_INCOMPLETE'],
            'enregistree_le' => '2026-10-03 18:00:00', 'enregistree_par' => null]);
        $ligne = $rencontres->parId($r);
        self::assertSame(['enregistree', 20, ['FEUILLE_INCOMPLETE']], [$ligne['statut'], (int) $ligne['total_pour'], json_decode($ligne['alertes'], true)]);
        self::assertCount(1, $rencontres->avecAlertes($saisonId));
        $rencontres->marquerAlertesVues($r);
        self::assertCount(0, $rencontres->avecAlertes($saisonId));
    }

    public function testJoueursEtParties(): void
    {
        $golfs = $this->app->service(GolfRepository::class);
        $joueurs = $this->app->service(JoueurRepository::class);
        $g = $golfs->trouverOuCreer('ORANGE');
        $a = $joueurs->trouverOuCreer($g, 'Caboche', 'H', 8.2, null);
        self::assertSame($a, $joueurs->trouverOuCreer($g, 'CABOCHE', null, 8.4, null));
        self::assertSame('8.4', $joueurs->parId($a)['dernier_index']);
        $b = $joueurs->trouverOuCreer($g, 'CABOCHE J', 'H', 12.0, null);
        self::assertNotSame($a, $b);
        self::assertSame(['CABOCHE', 'CABOCHE J'], array_column($joueurs->chercher($g, 'cab'), 'nom'));

        // fusion : les parties de b passent à a
        $saisons = $this->app->service(SaisonRepository::class);
        $series = $this->app->service(SerieRepository::class);
        $equipes = $this->app->service(EquipeRepository::class);
        $divisions = $this->app->service(DivisionRepository::class);
        $journees = $this->app->service(JourneeRepository::class);
        $rencontres = $this->app->service(RencontreRepository::class);
        $parties = $this->app->service(PartieRepository::class);
        $s = $saisons->creer('2026-27', '2026-09-01', '2027-06-30');
        $h1 = $series->parCode('H1')['id'];
        $e1 = $equipes->creer(['golf_id' => $g, 'serie_id' => $h1, 'nom' => 'ORANGE']);
        $e2 = $equipes->creer(['golf_id' => $golfs->trouverOuCreer('SALON'), 'serie_id' => $h1, 'nom' => 'SALON']);
        $d = $divisions->creer($s, $h1, 'DIVISION 1', 1);
        $j = $journees->creer($s, $h1, 1, 'aller', '2026-10-24');
        $r = $rencontres->creer($d, $j, $e1, $e2, '2026-10-24');
        $vide = ['rec_joueur1_id' => null, 'rec_index1' => null, 'rec_sexe1' => null, 'rec_joueur2_id' => null, 'rec_index2' => null, 'rec_sexe2' => null,
            'inv_joueur1_id' => null, 'inv_index1' => null, 'inv_sexe1' => null, 'inv_joueur2_id' => null, 'inv_index2' => null, 'inv_sexe2' => null,
            'resultat' => null, 'score_trous' => null, 'score_restants' => null, 'score_as' => 0, 'pts_pour' => 0, 'pts_contre' => 0];
        $parties->remplacer($r, [
            ['numero' => 1, 'type' => 'double', 'rec_joueur1_id' => $b, 'rec_index1' => 12.0, 'rec_sexe1' => 'H'] + $vide,
            ['numero' => 2, 'type' => 'simple', 'rec_joueur1_id' => $a, 'rec_index1' => 8.4, 'rec_sexe1' => 'H', 'resultat' => 'G', 'pts_pour' => 3, 'pts_contre' => 1] + $vide,
        ]);
        self::assertSame(['CABOCHE J', 'CABOCHE'], array_column($parties->parRencontre($r), 'rec_nom1'));
        self::assertSame(1, $joueurs->fusionner($b, $a));
        self::assertSame(['CABOCHE', 'CABOCHE'], array_column($parties->parRencontre($r), 'rec_nom1'));
        self::assertSame((string) $a, (string) $joueurs->parId($b)['fusionne_dans']);
        self::assertSame(['CABOCHE'], array_column($joueurs->chercher($g, 'cab'), 'nom'));
    }

    public function testUtilisateurs(): void
    {
        $series = $this->app->service(SerieRepository::class);
        $u = $this->app->service(UtilisateurRepository::class);
        $m = $series->parCode('M')['id']; $h1 = $series->parCode('H1')['id'];
        $idM = $u->creer(['identifiant' => 'SALON', 'serie_id' => $m, 'equipe_id' => null, 'hash_sha1' => sha1('a'), 'est_admin' => 0]);
        $idH = $u->creer(['identifiant' => 'SALON', 'serie_id' => $h1, 'equipe_id' => null, 'hash_sha1' => sha1('b'), 'est_admin' => 0]);
        $idA = $u->creer(['identifiant' => 'ADMIN', 'serie_id' => null, 'equipe_id' => null, 'hash_sha1' => sha1('c'), 'est_admin' => 1]);
        self::assertSame($idM, $u->parIdentifiantEtSerie('SALON', $m)['id']);
        self::assertSame($idH, $u->parIdentifiantEtSerie('salon', $h1)['id']);
        self::assertSame($idA, $u->parIdentifiantEtSerie('ADMIN', $m)['id']);
        self::assertNull($u->parIdentifiantEtSerie('INCONNU', $m));
        $u->enregistrerEchec($idM, '2030-01-01 00:00:00');
        self::assertSame([1, '2030-01-01 00:00:00'], [(int) $u->parId($idM)['tentatives'], $u->parId($idM)['bloque_jusqua']]);
        $u->definirBcrypt($idM, '$2y$x');
        $u->enregistrerSucces($idM);
        $l = $u->parId($idM);
        self::assertSame([0, null, null, '$2y$x'], [(int) $l['tentatives'], $l['bloque_jusqua'], $l['hash_sha1'], $l['hash_bcrypt']]);
    }
}
