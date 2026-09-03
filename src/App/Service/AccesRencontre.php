<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Http\HttpException;
use Agca\App\Repository\RencontreRepository;

final class AccesRencontre
{
    public function __construct(private App $app) {}

    public function charger(int $rencontreId, ?array $utilisateur): array
    {
        $r = $this->app->service(RencontreRepository::class)->parId($rencontreId);
        if ($r === null) { throw new HttpException(404, 'Rencontre introuvable'); }
        if ($utilisateur === null || !$this->estConcerne($r, $utilisateur)) {
            throw new HttpException(403, 'Cette feuille n\'est visible que par les deux capitaines concernés et l\'administrateur.');
        }
        return $r;
    }

    public function estConcerne(array $r, array $u): bool
    {
        if (!empty($u['est_admin'])) { return true; }
        $e = (int) ($u['equipe_id'] ?? 0);
        return $e !== 0 && in_array($e, [(int) $r['recevant_id'], (int) $r['invite_id']], true);
    }

    public function peutSaisir(array $r, array $u): bool
    {
        if ($r['saison_statut'] !== 'active') { return false; }
        if (!empty($u['est_admin'])) { return true; }
        return (int) ($u['equipe_id'] ?? 0) === (int) $r['recevant_id'] && $r['statut'] === 'a_jouer';
    }

    public function peutModifierDate(array $r, array $u): bool
    {
        if ($r['saison_statut'] !== 'active') { return false; }
        if (!empty($u['est_admin'])) { return true; }
        return (int) ($u['equipe_id'] ?? 0) === (int) $r['recevant_id'] && $r['statut'] === 'a_jouer';
    }
}
