<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\DivisionRepository;
use Agca\App\Repository\RencontreRepository;
use Agca\Domain\Classement;

final class ClassementService
{
    public function __construct(private App $app) {}

    public function pourDivision(int $divisionId): array
    {
        $equipes = [];
        foreach ($this->app->service(DivisionRepository::class)->equipes($divisionId) as $e) {
            $equipes[(int) $e['equipe_id']] = $e['nom'];
        }
        return Classement::calculer($equipes, $this->app->service(RencontreRepository::class)->parDivision($divisionId));
    }
}
