<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use PHPUnit\Framework\TestCase;

final class SanityTest extends TestCase
{
    public function testAutoloadEtPhpUnitFonctionnent(): void
    {
        self::assertTrue(class_exists(\PHPMailer\PHPMailer\PHPMailer::class));
        self::assertSame(80, PHP_VERSION_ID >= 80200 ? 80 : 0);
    }
}
