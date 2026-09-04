<?php
declare(strict_types=1);
namespace Agca\Domain;

/**
 * Markdown réduit, sûr : le texte est échappé AVANT toute transformation.
 * Blocs : #/## titres (rendus h2/h3), paragraphes, listes - et 1., tableaux |a|b|.
 * En ligne : **gras**, *italique*, [texte](url) avec url http(s), /relatif, mailto, #ancre.
 */
final class Markdown
{
    public static function rendre(string $md): string
    {
        $texte = str_replace(["\r\n", "\r"], "\n", $md);
        $texte = htmlspecialchars($texte, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
        $lignes = explode("\n", $texte);
        $blocs = []; $i = 0; $n = count($lignes);
        while ($i < $n) {
            $l = $lignes[$i];
            if (trim($l) === '') { $i++; continue; }
            if (preg_match('/^(#{1,2})\s+(.+)$/', $l, $m)) {
                $niveau = strlen($m[1]) + 1;
                $blocs[] = "<h$niveau>" . self::enLigne(trim($m[2])) . "</h$niveau>"; $i++; continue;
            }
            if (preg_match('/^\s*[-*]\s+/', $l)) {
                $items = [];
                while ($i < $n && preg_match('/^\s*[-*]\s+(.*)$/', $lignes[$i], $m)) { $items[] = '<li>' . self::enLigne(trim($m[1])) . '</li>'; $i++; }
                $blocs[] = "<ul>\n" . implode("\n", $items) . "\n</ul>"; continue;
            }
            if (preg_match('/^\s*\d+[.)]\s+/', $l)) {
                $items = [];
                while ($i < $n && preg_match('/^\s*\d+[.)]\s+(.*)$/', $lignes[$i], $m)) { $items[] = '<li>' . self::enLigne(trim($m[1])) . '</li>'; $i++; }
                $blocs[] = "<ol>\n" . implode("\n", $items) . "\n</ol>"; continue;
            }
            if (str_starts_with(trim($l), '|')) {
                $rangees = [];
                while ($i < $n && str_starts_with(trim($lignes[$i]), '|')) { $rangees[] = trim($lignes[$i]); $i++; }
                $blocs[] = self::tableau($rangees); continue;
            }
            $para = [];
            while ($i < $n && trim($lignes[$i]) !== '' && !preg_match('/^(#{1,2}\s|\s*[-*]\s|\s*\d+[.)]\s|\s*\|)/', $lignes[$i])) { $para[] = trim($lignes[$i]); $i++; }
            $blocs[] = '<p>' . implode("<br>\n", array_map([self::class, 'enLigne'], $para)) . '</p>';
        }
        return implode("\n", $blocs);
    }

    public static function texteBrut(string $md, int $max = 200): string
    {
        $t = html_entity_decode(strip_tags(self::rendre($md)), ENT_QUOTES, 'UTF-8');
        $t = trim((string) preg_replace('/\s+/u', ' ', str_replace("\n", ' ', $t)));
        if (mb_strlen($t) > $max) { $t = rtrim(mb_substr($t, 0, $max)) . '…'; }
        return $t;
    }

    /** @param list<string> $rangees lignes commençant par | */
    private static function tableau(array $rangees): string
    {
        $cellules = fn(string $r): array => array_map('trim', explode('|', trim(trim($r), '|')));
        $tete = $cellules($rangees[0]);
        $corps = array_slice($rangees, 1);
        if ($corps !== [] && preg_match('/^\|[\s:\-|]+\|?$/', $corps[0])) { array_shift($corps); }
        $html = "<table>\n<thead><tr>" . implode('', array_map(fn($c) => '<th>' . self::enLigne($c) . '</th>', $tete)) . "</tr></thead>\n<tbody>\n";
        foreach ($corps as $r) { $html .= '<tr>' . implode('', array_map(fn($c) => '<td>' . self::enLigne($c) . '</td>', $cellules($r))) . "</tr>\n"; }
        return $html . "</tbody>\n</table>";
    }

    private static function enLigne(string $t): string
    {
        $liens = [];
        $t = preg_replace_callback('/\[([^\]]+)\]\(([^\s]+)\)/', function (array $m) use (&$liens): string {
            $url = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
            if (!preg_match('#^(https?://|/(?!/)|mailto:|\#)#i', $url)) { return $m[1]; }
            $html = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" rel="noopener">' . self::formater($m[1]) . '</a>';
            $liens[] = $html;
            return "\x00" . (count($liens) - 1) . "\x00";
        }, $t) ?? $t;
        $t = self::formater($t);
        foreach ($liens as $i => $html) { $t = str_replace("\x00" . $i . "\x00", $html, $t); }
        return $t;
    }

    private static function formater(string $t): string
    {
        $t = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $t) ?? $t;
        $t = preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/s', '<em>$1</em>', $t) ?? $t;
        return $t;
    }
}
