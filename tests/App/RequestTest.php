<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\Http\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    /** HEAD est résolue comme une GET (mêmes routes), en retenant qu'aucun corps ne doit être émis. */
    public function testDepuisGlobalesTraiteHeadCommeGet(): void
    {
        $serveurAvant = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $_SERVER['REQUEST_URI'] = '/';
        try {
            $req = Request::depuisGlobales();
            self::assertSame('GET', $req->methode());
            self::assertTrue($req->estHead());
            self::assertFalse($req->estPost());
        } finally {
            $_SERVER = $serveurAvant;
        }
    }

    public function testDepuisGlobalesGetOrdinaireNestPasHead(): void
    {
        $serveurAvant = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        try {
            $req = Request::depuisGlobales();
            self::assertSame('GET', $req->methode());
            self::assertFalse($req->estHead());
        } finally {
            $_SERVER = $serveurAvant;
        }
    }

    public function testConstructeurEstHeadParDefautFaux(): void
    {
        $req = new Request('GET', '/', [], [], '');
        self::assertFalse($req->estHead());
    }

    public function testFichierValide(): void
    {
        $req = new Request('POST', '/', [], [], '', ['f' => ['name' => 'x.pdf', 'tmp_name' => '/tmp/php123', 'type' => 'application/pdf', 'size' => 10, 'error' => 0]]);
        self::assertSame('x.pdf', $req->fichier('f')['name']);
    }

    /** `name`/`tmp_name` non chaînes (upload avec un nom de champ en tableau) : refusé, retourne null. */
    public function testFichierRefuseNameNonChaine(): void
    {
        $req = new Request('POST', '/', [], [], '', ['f' => ['name' => ['x.pdf'], 'tmp_name' => '/tmp/php123', 'type' => 'application/pdf', 'size' => 10, 'error' => 0]]);
        self::assertNull($req->fichier('f'));
    }

    public function testFichierRefuseTmpNameNonChaine(): void
    {
        $req = new Request('POST', '/', [], [], '', ['f' => ['name' => 'x.pdf', 'tmp_name' => ['/tmp/php123'], 'type' => 'application/pdf', 'size' => 10, 'error' => 0]]);
        self::assertNull($req->fichier('f'));
    }

    public function testFichierAbsentRetourneNull(): void
    {
        $req = new Request('POST', '/', [], [], '', []);
        self::assertNull($req->fichier('f'));
    }
}
