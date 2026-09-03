<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Http\HttpException;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\EquipeRepository;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\JourneeRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\App\Repository\SaisonRepository;
use Agca\App\Repository\SerieRepository;
use Agca\Domain\Grille;

final class CreationSaison
{
    public function __construct(private App $app) {}

    /** @return array{id:?int, erreurs:list<string>} */
    public function creerSaison(string $libelle, string $debut, string $fin, array $datesParSerie, array $admin): array
    {
        $this->gardeAdmin($admin);
        $erreurs = [];
        $libelle = trim($libelle);
        if ($libelle === '') { $erreurs[] = 'Libellé de saison vide.'; }
        $d = EnregistrementFeuille::lireDate($debut); $f = EnregistrementFeuille::lireDate($fin);
        if ($d === null || $f === null || $d > $f) { $erreurs[] = 'Dates de saison invalides.'; }
        $journees = [];
        foreach ($datesParSerie as $code => $phases) {
            $serie = $this->app->service(SerieRepository::class)->parCode((string) $code);
            if ($serie === null) { $erreurs[] = "Série inconnue : $code"; continue; }
            foreach (['aller', 'retour'] as $phase) {
                $numero = 0;
                foreach ($phases[$phase] ?? [] as $date) {
                    if (trim((string) $date) === '') { continue; }
                    $ymd = EnregistrementFeuille::lireDate((string) $date);
                    if ($ymd === null) { $erreurs[] = "Série $code, $phase : date illisible « $date »."; continue; }
                    $journees[] = [(int) $serie['id'], ++$numero, $phase, $ymd];
                }
            }
        }
        if ($erreurs !== []) { return ['id' => null, 'erreurs' => $erreurs]; }
        $id = $this->app->db()->transaction(function () use ($libelle, $d, $f, $journees, $admin) {
            $saisons = $this->app->service(SaisonRepository::class);
            foreach ($saisons->toutes() as $s) { if ($s['statut'] === 'active') { $saisons->changerStatut((int) $s['id'], 'gelee'); } }
            $id = $saisons->creer($libelle, $d, $f);
            foreach ($journees as [$serieId, $numero, $phase, $ymd]) { $this->app->service(JourneeRepository::class)->creer($id, $serieId, $numero, $phase, $ymd); }
            $this->app->service(JournalRepository::class)->ecrire((int) $admin['id'], 'saison_creee', 'saison', $id, ['libelle' => $libelle]);
            return $id;
        });
        return ['id' => $id, 'erreurs' => []];
    }

    /** @param array<int,int> $equipesParPosition position => equipe_id ; @return array{id:?int, erreurs:list<string>} */
    public function ajouterDivision(int $saisonId, int $serieId, string $libelle, array $equipesParPosition, array $admin): array
    {
        $this->gardeAdmin($admin);
        $erreurs = [];
        $libelle = trim($libelle);
        if ($libelle === '') { $erreurs[] = 'Libellé de division vide.'; }
        $positions = array_keys($equipesParPosition); sort($positions);
        $n = count($positions);
        if (!in_array($n, [4, 5], true) || $positions !== range(1, $n)) { $erreurs[] = 'Une division compte 4 ou 5 équipes aux positions 1 à ' . $n . ' sans trou.'; }
        $ids = array_map('intval', array_values($equipesParPosition));
        if (count(array_unique($ids)) !== count($ids)) { $erreurs[] = 'Une même équipe apparaît deux fois.'; }
        $divisions = $this->app->service(DivisionRepository::class);
        foreach ($ids as $e) {
            $eq = $this->app->service(EquipeRepository::class)->parId($e);
            if ($eq === null || (int) $eq['serie_id'] !== $serieId) { $erreurs[] = "Équipe $e inconnue ou d'une autre série."; continue; }
            $deja = $divisions->divisionDeLEquipe($e, $saisonId);
            if ($deja !== null) { $erreurs[] = "{$eq['nom']} est déjà dans la division {$deja['libelle']}."; }
        }
        $journees = [];
        foreach ($this->app->service(JourneeRepository::class)->parSaisonEtSerie($saisonId, $serieId) as $j) { $journees[$j['phase']][(int) $j['numero']] = $j; }
        if ($erreurs === []) {
            $nb = Grille::nbJournees($n);
            foreach (['aller', 'retour'] as $phase) {
                for ($k = 1; $k <= $nb; $k++) { if (!isset($journees[$phase][$k])) { $erreurs[] = "Il manque la journée $phase n°$k au calendrier de la série."; } }
            }
        }
        if ($erreurs !== []) { return ['id' => null, 'erreurs' => $erreurs]; }
        $id = $this->app->db()->transaction(function () use ($saisonId, $serieId, $libelle, $equipesParPosition, $journees, $n, $admin, $divisions) {
            $ordre = count($divisions->parSaisonEtSerie($saisonId, $serieId)) + 1;
            $id = $divisions->creer($saisonId, $serieId, $libelle, $ordre);
            foreach ($equipesParPosition as $pos => $e) { $divisions->ajouterEquipe($id, (int) $e, (int) $pos); }
            foreach (Grille::generer($n) as $m) {
                $j = $journees[$m['phase']][$m['journee']];
                $this->app->service(RencontreRepository::class)->creer($id, (int) $j['id'], (int) $equipesParPosition[$m['recevant']], (int) $equipesParPosition[$m['invite']], $j['date_calendrier']);
            }
            $this->app->service(JournalRepository::class)->ecrire((int) $admin['id'], 'division_creee', 'division', $id, ['libelle' => $libelle, 'equipes' => $equipesParPosition]);
            return $id;
        });
        return ['id' => $id, 'erreurs' => []];
    }

    public function geler(int $saisonId, array $admin): void
    {
        $this->gardeAdmin($admin);
        $this->app->service(SaisonRepository::class)->changerStatut($saisonId, 'gelee');
        $this->app->service(JournalRepository::class)->ecrire((int) $admin['id'], 'saison_gelee', 'saison', $saisonId);
    }

    public function activer(int $saisonId, array $admin): void
    {
        $this->gardeAdmin($admin);
        $saisons = $this->app->service(SaisonRepository::class);
        foreach ($saisons->toutes() as $s) { if ($s['statut'] === 'active') { $saisons->changerStatut((int) $s['id'], 'gelee'); } }
        $saisons->changerStatut($saisonId, 'active');
        $this->app->service(JournalRepository::class)->ecrire((int) $admin['id'], 'saison_activee', 'saison', $saisonId);
    }

    private function gardeAdmin(array $u): void
    {
        if (empty($u['est_admin'])) { throw new HttpException(403, 'Réservé à l\'administrateur'); }
    }
}
