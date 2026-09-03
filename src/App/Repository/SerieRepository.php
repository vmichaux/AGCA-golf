<?php
declare(strict_types=1);
namespace Agca\App\Repository;

use Agca\Domain\Serie;

final class SerieRepository extends Repository
{
    public function parCode(string $code): ?array { return $this->enrichir($this->db->one('SELECT * FROM agca_serie WHERE code = ?', [strtoupper($code)])); }
    public function parId(int $id): ?array { return $this->enrichir($this->db->one('SELECT * FROM agca_serie WHERE id = ?', [$id])); }
    public function toutes(): array { return array_map(fn($l) => $this->enrichir($l), $this->db->all('SELECT * FROM agca_serie ORDER BY ordre')); }

    private function enrichir(?array $l): ?array
    {
        if ($l === null) { return null; }
        $l['serie'] = Serie::depuisLigne($l);
        return $l;
    }
}
