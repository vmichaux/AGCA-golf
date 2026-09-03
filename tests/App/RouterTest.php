<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testResoudChemininStatiqueEtParametres(): void
    {
        $r = new Router();
        $r->get('/', ['A', 'accueil']);
        $r->get('/serie/{code}/classements', ['A', 'classements']);
        $r->post('/rencontre/{id}/saisie', ['B', 'enregistrer']);

        self::assertSame(['handler' => ['A', 'accueil'], 'params' => []], $r->resoudre('GET', '/'));
        self::assertSame(['handler' => ['A', 'classements'], 'params' => ['code' => 'H1']], $r->resoudre('GET', '/serie/H1/classements'));
        self::assertSame(['handler' => ['B', 'enregistrer'], 'params' => ['id' => '12']], $r->resoudre('POST', '/rencontre/12/saisie'));
        self::assertNull($r->resoudre('GET', '/rencontre/12/saisie'));
        self::assertNull($r->resoudre('GET', '/inconnu'));
    }

    public function testSlashFinalIgnore(): void
    {
        $r = new Router();
        $r->get('/capitaine', ['A', 'tableau']);
        self::assertNotNull($r->resoudre('GET', '/capitaine/'));
    }
}
