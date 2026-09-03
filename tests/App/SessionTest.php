<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\Session;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    protected function setUp(): void { $_SESSION = []; }

    public function testFlashsConsommesUneFois(): void
    {
        $s = new Session(); $s->demarrer();
        $s->flash('succes', 'Feuille enregistrée');
        self::assertSame([['type' => 'succes', 'message' => 'Feuille enregistrée']], $s->consommerFlashs());
        self::assertSame([], $s->consommerFlashs());
    }

    public function testCsrfStableEtVerifie(): void
    {
        $s = new Session(); $s->demarrer();
        $t = $s->csrf();
        self::assertSame($t, $s->csrf());
        self::assertTrue($s->verifierCsrf($t));
        self::assertFalse($s->verifierCsrf('autre'));
        self::assertFalse($s->verifierCsrf(null));
    }
}
