#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../vendor/autoload.php';

use Agca\App\App;
use Agca\App\Config;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\GolfRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;
use Agca\App\Service\CreationSaison;
use Agca\App\Service\EnregistrementFeuille;
use Agca\Domain\MotDePasse;

$app = new App(Config::charger($argv[1] ?? null));
if ($app->service(SaisonRepository::class)->toutes() !== []) { fwrite(STDERR, "Une saison existe déjà : démo refusée.\n"); exit(1); }
$admin = ['id' => null, 'identifiant' => 'demo', 'est_admin' => 1];
$series = $app->service(SerieRepository::class); $golfs = $app->service(GolfRepository::class); $equipes = $app->service(EquipeRepository::class); $users = $app->service(UtilisateurRepository::class);
$adminId = $users->creer(['identifiant' => 'ADMIN', 'serie_id' => null, 'hash_bcrypt' => MotDePasse::hacher('admin1234'), 'est_admin' => 1]);
$ids = [];
foreach (['M' => ['SALON', 'FREGATE', 'ORANGE', 'DIGNE'], 'H1' => ['VALGARDE-1', 'SALON', 'ORANGE', 'VICTORIA']] as $code => $noms) {
    $serieId = (int) $series->parCode($code)['id'];
    foreach ($noms as $pos => $nom) {
        $e = $equipes->creer(['golf_id' => $golfs->trouverOuCreer(preg_replace('/-\d$/', '', $nom)), 'serie_id' => $serieId, 'nom' => $nom, 'capitaine_email' => strtolower($nom) . '@exemple.fr']);
        $users->creer(['identifiant' => $nom, 'serie_id' => $serieId, 'equipe_id' => $e, 'hash_bcrypt' => MotDePasse::hacher('demo1234')]);
        $ids[$code][$pos + 1] = $e;
    }
}
$c = $app->service(CreationSaison::class);
$s = $c->creerSaison('DEMO', date('Y-m-d', strtotime('-30 days')), date('Y-m-d', strtotime('+200 days')),
    ['M' => ['aller' => [date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+7 days')), date('Y-m-d', strtotime('+21 days'))], 'retour' => [date('Y-m-d', strtotime('+60 days')), date('Y-m-d', strtotime('+74 days')), date('Y-m-d', strtotime('+88 days'))]],
     'H1' => ['aller' => [date('Y-m-d', strtotime('-3 days')), date('Y-m-d', strtotime('+14 days')), date('Y-m-d', strtotime('+28 days'))], 'retour' => [date('Y-m-d', strtotime('+67 days')), date('Y-m-d', strtotime('+81 days')), date('Y-m-d', strtotime('+95 days'))]]], $admin);
$c->ajouterDivision($s['id'], (int) $series->parCode('M')['id'], 'DIV2/POULE B', $ids['M'], $admin);
$c->ajouterDivision($s['id'], (int) $series->parCode('H1')['id'], 'DIVISION 1', $ids['H1'], $admin);
// Une feuille M déjà saisie par SALON (recevant J1 aller contre FREGATE).
$divM = $app->db()->one('SELECT id FROM agca_division WHERE libelle = ?', ['DIV2/POULE B']);
$r = $app->service(RencontreRepository::class)->parDivision((int) $divM['id'])[0];
$post = ['date_reelle' => $r['date_reelle'], 'p' => []];
$res = ['G', 'G', 'P', 'N', 'G', 'G', 'P', 'G', 'G', 'G', 'P', 'G', 'N', 'G', 'P'];
foreach (range(1, 15) as $n) {
    $post['p'][$n] = ['rec1_nom' => "Salon$n", 'rec1_index' => 12 + $n % 9, 'rec1_sexe' => $n % 3 ? 'H' : 'D', 'rec2_nom' => "Salonbis$n", 'rec2_index' => 15, 'rec2_sexe' => 'H',
        'inv1_nom' => "Fregate$n", 'inv1_index' => 13 + $n % 8, 'inv1_sexe' => 'H', 'inv2_nom' => "Fregatebis$n", 'inv2_index' => 16, 'inv2_sexe' => 'D', 'resultat' => $res[$n - 1], 'score' => $res[$n - 1] === 'N' ? 'AS' : '2&1'];
}
$app->service(EnregistrementFeuille::class)->enregistrer((int) $r['id'], $post, $users->parIdentifiantEtSerie('SALON', (int) $series->parCode('M')['id']));
echo "Démo prête : ADMIN/admin1234, capitaines (SALON, FREGATE, ORANGE, DIGNE en M ; VALGARDE-1, SALON, ORANGE, VICTORIA en H1) / demo1234.\n";
