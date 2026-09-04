<?php
declare(strict_types=1);
namespace Agca\Tests\Domain;

use Agca\Domain\Markdown;
use PHPUnit\Framework\TestCase;

final class MarkdownTest extends TestCase
{
    public function testParagraphesEtTitres(): void
    {
        $html = Markdown::rendre("# Titre\n\nUn paragraphe\nsur deux lignes.\n\n## Sous-titre\n\nAutre.");
        self::assertSame("<h2>Titre</h2>\n<p>Un paragraphe<br>\nsur deux lignes.</p>\n<h3>Sous-titre</h3>\n<p>Autre.</p>", $html);
    }

    public function testGrasItaliqueEtLiens(): void
    {
        self::assertSame('<p>Du <strong>gras</strong>, de l\'<em>italique</em> et un <a href="https://www.ffgolf.org/" rel="noopener">lien</a>.</p>',
            Markdown::rendre("Du **gras**, de l'*italique* et un [lien](https://www.ffgolf.org/)."));
    }

    public function testListes(): void
    {
        self::assertSame("<ul>\n<li>un</li>\n<li>deux</li>\n</ul>\n<ol>\n<li>a</li>\n<li>b</li>\n</ol>", Markdown::rendre("- un\n- deux\n\n1. a\n2. b"));
    }

    public function testTableau(): void
    {
        $html = Markdown::rendre("| Rang | Golf |\n|---|---|\n| 1 | Valgarde |\n| 2 | Orange |");
        self::assertSame("<table>\n<thead><tr><th>Rang</th><th>Golf</th></tr></thead>\n<tbody>\n<tr><td>1</td><td>Valgarde</td></tr>\n<tr><td>2</td><td>Orange</td></tr>\n</tbody>\n</table>", $html);
    }

    public function testHtmlEchappeEtLiensDangereuxRefuses(): void
    {
        self::assertSame('<p>&lt;script&gt;alert(1)&lt;/script&gt; &amp; fin</p>', Markdown::rendre('<script>alert(1)</script> & fin'));
        self::assertSame('<p>lien</p>', Markdown::rendre('[lien](javascript:alert(1))'));
        self::assertSame('<p>lien</p>', Markdown::rendre('[lien](data:text/html;base64,AAA)'));
        self::assertSame('<p><a href="/contact" rel="noopener">contact</a></p>', Markdown::rendre('[contact](/contact)'));
        self::assertSame('<p><a href="mailto:a@b.fr" rel="noopener">mail</a></p>', Markdown::rendre('[mail](mailto:a@b.fr)'));
    }

    public function testGuillemetsDansLienEchappes(): void
    {
        self::assertSame('<p><a href="/x?a=1&amp;b=&quot;" rel="noopener">l</a></p>', Markdown::rendre('[l](/x?a=1&b=")'));
    }

    public function testTexteBrut(): void
    {
        self::assertSame('Titre Un paragraphe avec du gras et un lien.', Markdown::texteBrut("# Titre\n\nUn paragraphe avec du **gras** et un [lien](/x)."));
        self::assertSame('abcdefghij…', Markdown::texteBrut('abcdefghijklmnop', 10));
        self::assertSame('', Markdown::texteBrut(''));
    }

    public function testVideEtEspaces(): void
    {
        self::assertSame('', Markdown::rendre(''));
        self::assertSame('', Markdown::rendre("  \n\n  "));
        self::assertSame("<p>a</p>\n<p>b</p>", Markdown::rendre("a\r\n\r\nb"));
    }
}
