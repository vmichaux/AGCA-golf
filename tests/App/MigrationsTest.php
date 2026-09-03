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

    public function testDecoupageToleranteEspacesFinDeLigneEtCrlf(): void
    {
        $dir = sys_get_temp_dir() . '/agca_migrations_' . uniqid();
        mkdir($dir);
        $nom = '999_tmp.sql';
        try {
            file_put_contents(
                $dir . '/' . $nom,
                "CREATE TABLE agca_tmp_a (id INT);   \r\n-- commentaire\nCREATE TABLE agca_tmp_b (id INT);\n"
            );

            $appliques = \Agca\App\Migrations::appliquer($this->db, $dir);

            self::assertSame([$nom], $appliques);
            $tables = array_column($this->db->all("SELECT TABLE_NAME AS t FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('agca_tmp_a', 'agca_tmp_b') ORDER BY 1"), 't');
            self::assertSame(['agca_tmp_a', 'agca_tmp_b'], $tables);
            $noms = array_column($this->db->all('SELECT nom FROM agca_migration WHERE nom = ?', [$nom]), 'nom');
            self::assertSame([$nom], $noms);
        } finally {
            $this->db->pdo()->exec('DROP TABLE IF EXISTS agca_tmp_a');
            $this->db->pdo()->exec('DROP TABLE IF EXISTS agca_tmp_b');
            @unlink($dir . '/' . $nom);
            @rmdir($dir);
        }
    }
}
