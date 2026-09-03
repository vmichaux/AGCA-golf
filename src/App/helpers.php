<?php
declare(strict_types=1);

function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** « sam. 26 sept. 2026 » à partir de Y-m-d ; chaîne vide si null. */
function fmt_date(?string $ymd): string
{
    if ($ymd === null || $ymd === '') { return ''; }
    $d = \DateTimeImmutable::createFromFormat('Y-m-d', substr($ymd, 0, 10));
    if ($d === false) { return $ymd; }
    $jours = ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'];
    $mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    return $jours[(int) $d->format('w')] . ' ' . $d->format('j') . ' ' . $mois[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
}

/** Points : « 3 », « 2,5 ». */
function fmt_pts(float|int|string $v): string
{
    $f = (float) $v;
    return $f === floor($f) ? (string) (int) $f : str_replace('.', ',', rtrim(rtrim(number_format($f, 1, '.', ''), '0'), '.'));
}
