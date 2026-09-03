<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use Agca\Domain\MotDePasse;
use PHPUnit\Framework\TestCase;

final class MotDePasseTest extends TestCase
{
    public function testVerifieUneEmpreinteSha1Legacy(): void
    {
        self::assertTrue(MotDePasse::verifier('golf2026', sha1('golf2026'), null));
        self::assertFalse(MotDePasse::verifier('autre', sha1('golf2026'), null));
        self::assertTrue(MotDePasse::doitMigrer(sha1('golf2026'), null));
    }

    public function testBcryptPrioritaire(): void
    {
        $h = MotDePasse::hacher('nouveau');
        self::assertStringStartsWith('$2y$', $h);
        self::assertTrue(MotDePasse::verifier('nouveau', sha1('ancien'), $h));
        self::assertFalse(MotDePasse::verifier('ancien', sha1('ancien'), $h));
        self::assertFalse(MotDePasse::doitMigrer(null, $h));
    }

    public function testAucuneEmpreinteRefuse(): void
    {
        self::assertFalse(MotDePasse::verifier('x', null, null));
        self::assertFalse(MotDePasse::verifier('', sha1(''), null));
    }

    public function testGenerer(): void
    {
        $p = MotDePasse::generer();
        self::assertSame(12, strlen($p));
        self::assertMatchesRegularExpression('/^[a-z0-9]+$/', $p);
        self::assertNotSame($p, MotDePasse::generer());
    }
}
