<?php
declare(strict_types=1);
namespace Agca\App;

final class Migrations
{
    /** @return list<string> noms des fichiers appliqués */
    public static function appliquer(Db $db, string $dossier): array
    {
        $db->exec('CREATE TABLE IF NOT EXISTS agca_migration (nom VARCHAR(100) PRIMARY KEY, applique_le DATETIME NOT NULL) ENGINE=InnoDB');
        $faites = array_column($db->all('SELECT nom FROM agca_migration'), 'nom');
        $fichiers = glob(rtrim($dossier, '/') . '/*.sql') ?: [];
        sort($fichiers);
        $appliques = [];
        foreach ($fichiers as $f) {
            $nom = basename($f);
            if (in_array($nom, $faites, true)) { continue; }
            $sql = file_get_contents($f);
            foreach (self::decouper($sql) as $i => $stmt) {
                try {
                    $db->pdo()->exec($stmt);
                } catch (\PDOException $e) {
                    throw new \RuntimeException("Migration $nom, instruction n°$i : " . $e->getMessage(), 0, $e);
                }
            }
            $db->exec('INSERT INTO agca_migration (nom, applique_le) VALUES (?, NOW())', [$nom]);
            $appliques[] = $nom;
        }
        return $appliques;
    }

    /** Supprime toutes les tables agca_* (tests uniquement). */
    public static function reinitialiser(Db $db): void
    {
        $db->pdo()->exec('SET FOREIGN_KEY_CHECKS = 0');
        $tables = $db->all("SELECT TABLE_NAME AS t FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'agca\\_%'");
        foreach ($tables as $t) { $db->pdo()->exec('DROP TABLE IF EXISTS `' . $t['t'] . '`'); }
        $db->pdo()->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /** @return list<string> */
    private static function decouper(string $sql): array
    {
        $sql = str_replace("\r\n", "\n", $sql);
        $sansCommentaires = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $morceaux = preg_split('/;[ \t]*\r?\n/', $sansCommentaires . "\n") ?: [];
        return array_values(array_filter(array_map('trim', $morceaux), fn($s) => $s !== ''));
    }
}
