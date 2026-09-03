<?php
declare(strict_types=1);
namespace Agca\Tests\App;

final class MigrationsTest extends DbTestCase
{
    public function testSchemaCreeEtSeriesInserees(): void
    {
        $tables = array_column($this->db->all("SELECT TABLE_NAME AS t FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'agca\\_%' ORDER BY 1"), 't');
        foreach (['agca_saison', 'agca_serie', 'agca_golf', 'agca_equipe', 'agca_utilisateur', 'agca_division', 'agca_division_equipe', 'agca_journee', 'agca_rencontre', 'agca_partie', 'agca_joueur', 'agca_journal', 'agca_relance', 'agca_migration'] as $t) {
            self::assertContains($t, $tables);
        }
        $series = $this->db->all('SELECT code, structure FROM agca_serie ORDER BY ordre');
        self::assertSame([['code' => 'M', 'structure' => 'SSDSSDSSDSSDSSD'], ['code' => 'H1', 'structure' => 'DSSSS']], $series);
    }

    public function testRejouerNAppliqueRien(): void
    {
        self::assertSame([], \Agca\App\Migrations::appliquer($this->db, dirname(__DIR__, 2) . '/db/migrations'));
    }
}
