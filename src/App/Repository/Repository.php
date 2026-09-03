<?php
declare(strict_types=1);
namespace Agca\App\Repository;

use Agca\App\App;
use Agca\App\Db;

abstract class Repository
{
    protected Db $db;

    public function __construct(protected App $app)
    {
        $this->db = $app->db();
    }

    /** Construit « SET a = :a, b = :b » et le tableau de paramètres à partir d'un tableau associatif. */
    protected function set(array $champs): array
    {
        $parts = []; $params = [];
        foreach ($champs as $k => $v) {
            $parts[] = "`$k` = :$k";
            $params[$k] = is_bool($v) ? (int) $v : (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v);
        }
        return [implode(', ', $parts), $params];
    }
}
