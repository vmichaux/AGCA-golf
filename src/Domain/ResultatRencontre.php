<?php
declare(strict_types=1);
namespace Agca\Domain;

final class ResultatRencontre
{
    /** @param list<array{0:int,1:int}> $ptsParties */
    public function __construct(
        public readonly int $totalPour,
        public readonly int $totalContre,
        public readonly float $ptsPour,
        public readonly float $ptsContre,
        public readonly float $bonusInvite,
        public readonly array $ptsParties,
    ) {}

    public function pointsTotauxRecevant(): float { return $this->ptsPour; }
    public function pointsTotauxInvite(): float { return $this->ptsContre + $this->bonusInvite; }
}
