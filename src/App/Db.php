<?php
declare(strict_types=1);
namespace Agca\App;

final class Db
{
    private \PDO $pdo;

    public function __construct(string $dsn, string $user, string $pass)
    {
        $this->pdo = new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->pdo->exec('SET NAMES utf8mb4');
    }

    /** @param array<string, mixed> $cfg tableau complet de config */
    public static function depuisConfig(array $cfg): self
    {
        return new self($cfg['db']['dsn'], $cfg['db']['user'], $cfg['db']['pass']);
    }

    public function pdo(): \PDO { return $this->pdo; }

    /** @return list<array<string, mixed>> */
    public function all(string $sql, array $p = []): array
    {
        $st = $this->pdo->prepare($sql); $st->execute($p);
        return $st->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function one(string $sql, array $p = []): ?array
    {
        $st = $this->pdo->prepare($sql); $st->execute($p);
        $r = $st->fetch();
        return $r === false ? null : $r;
    }

    public function exec(string $sql, array $p = []): int
    {
        $st = $this->pdo->prepare($sql); $st->execute($p);
        return $st->rowCount();
    }

    public function insert(string $sql, array $p = []): int
    {
        $this->exec($sql, $p);
        return (int) $this->pdo->lastInsertId();
    }

    /** Exécute $fn dans une transaction (imbrication tolérée : réutilise la transaction ouverte). */
    public function transaction(callable $fn): mixed
    {
        if ($this->pdo->inTransaction()) { return $fn($this); }
        $this->pdo->beginTransaction();
        try {
            $r = $fn($this);
            $this->pdo->commit();
            return $r;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
