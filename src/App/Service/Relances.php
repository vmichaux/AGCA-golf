<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\RelanceRepository;
use Agca\App\Repository\RencontreRepository;

final class Relances
{
    public const DELAI_JOURS = 2;
    public const INTERVALLE_JOURS = 2;

    public function __construct(private App $app) {}

    /** @return array{envoyees:int, rencontres:list<int>} */
    public function executer(\DateTimeImmutable $maintenant): array
    {
        $limite = $maintenant->modify('-' . self::DELAI_JOURS . ' days')->format('Y-m-d');
        $cibles = $this->app->service(RencontreRepository::class)->aRelancer($limite, self::INTERVALLE_JOURS, $maintenant->format('Y-m-d H:i:s'));
        $ids = [];
        foreach ($cibles as $r) {
            if (!$this->app->service(Notifications::class)->relance($r)) { continue; }
            $this->app->service(RelanceRepository::class)->enregistrer((int) $r['id'], $maintenant->format('Y-m-d H:i:s'));
            $this->app->service(JournalRepository::class)->ecrire(null, 'relance', 'rencontre', (int) $r['id'], ['a' => $r['recevant_email']]);
            $ids[] = (int) $r['id'];
        }
        return ['envoyees' => count($ids), 'rencontres' => $ids];
    }
}
