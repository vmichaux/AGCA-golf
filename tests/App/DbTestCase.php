<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\Db;
use Agca\App\Migrations;
use PHPUnit\Framework\TestCase;

abstract class DbTestCase extends TestCase
{
    protected Db $db;

    protected function setUp(): void
    {
        $dsn = getenv('AGCA_TEST_DSN') ?: '';
        if ($dsn === '') { self::markTestSkipped('AGCA_TEST_DSN non défini'); }
        $this->db = new Db($dsn, getenv('AGCA_TEST_USER') ?: 'root', getenv('AGCA_TEST_PASS') ?: '');
        Migrations::reinitialiser($this->db);
        Migrations::appliquer($this->db, dirname(__DIR__, 2) . '/db/migrations');
    }

    /** @return array<string, mixed> config minimale pour App */
    protected function config(): array
    {
        return [
            'db' => ['dsn' => getenv('AGCA_TEST_DSN'), 'user' => getenv('AGCA_TEST_USER') ?: 'root', 'pass' => getenv('AGCA_TEST_PASS') ?: ''],
            'mail' => ['enabled' => false, 'host' => '', 'port' => 587, 'user' => '', 'pass' => '', 'from' => 'noreply@test', 'from_nom' => 'AGCA test', 'admin' => 'admin@test'],
            'app' => ['base_url' => 'http://test', 'secret' => 'secret-test', 'debug' => true, 'https' => false],
        ];
    }
}
