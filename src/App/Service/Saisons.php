<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\SaisonRepository;

final class Saisons
{
    public function __construct(private App $app) {}

    public function courante(?string $idDemande): ?array
    {
        $repo = $this->app->service(SaisonRepository::class);
        if ($idDemande !== null && ctype_digit($idDemande)) {
            $s = $repo->parId((int) $idDemande);
            if ($s !== null) { return $s; }
        }
        return $repo->active() ?? ($repo->toutes()[0] ?? null);
    }

    public function toutes(): array { return $this->app->service(SaisonRepository::class)->toutes(); }
}
