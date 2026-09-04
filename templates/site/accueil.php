<?php
/**
 * Accueil — reproduit la maquette validée (docs/maquette/accueil.body.html) avec les données réelles.
 * Les textes éditoriaux viennent des pages `agca_page` ; en leur absence, on retombe sur les textes
 * de la maquette. Seul `corps_html` (produit par Agca\Domain\Markdown) est affiché sans e().
 */
use Agca\Domain\Markdown;

/** Corps enrichi d'une page, ou le repli de la maquette (déjà du HTML de confiance). */
$corps = static fn(?array $p, string $repli): string
    => $p !== null && trim((string) $p['corps_html']) !== '' ? (string) $p['corps_html'] : $repli;
/** Titre d'une page, ou le repli de la maquette. */
$titrePage = static fn(?array $p, string $repli): string
    => $p !== null && trim((string) $p['titre']) !== '' ? (string) $p['titre'] : $repli;
/** Texte brut d'une page (citation, phrases courtes), ou le repli de la maquette. */
$brut = static fn(?array $p, string $repli): string
    => $p !== null && trim((string) $p['corps_md']) !== '' ? Markdown::texteBrut((string) $p['corps_md'], 400) : $repli;

$joursLongs = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$moisLongs = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$moisCourts = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
$jour = static fn(string $ymd): ?\DateTimeImmutable
    => \DateTimeImmutable::createFromFormat('Y-m-d', substr($ymd, 0, 10)) ?: null;
/** « samedi 26 septembre 2026 » */
$dateLongue = static function (string $ymd) use ($jour, $joursLongs, $moisLongs): string {
    $d = $jour($ymd);
    return $d === null ? $ymd : $joursLongs[(int) $d->format('w')] . ' ' . $d->format('j') . ' ' . $moisLongs[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
};
/** « 1re », « 2e » */
$rang = static fn(int $n): string => $n <= 1 ? '1re' : $n . 'e';

$serieDefaut = $classements[0]['serie']['code'] ?? ($series[0]['code'] ?? 'M');
$lienCapitaine = empty($utilisateur) ? '/connexion' : '/capitaine';
/** code de série => libellé, pour les rencontres (les alias joints ne portent que le code). */
$libelleSerie = [];
foreach ($series as $s) { $libelleSerie[(string) $s['code']] = (string) $s['libelle']; }
?>
<section class="bandeau"><div class="conteneur">
  <span class="etiquette">Interclubs · Provence-Alpes-Côte d'Azur</span>
  <h1>Association des Golfs de la Coupe de l'Amitié</h1>
  <?= $corps($pages['accueil-accroche'], '<p>' . e($accroche_repli) . '</p>') ?>
  <p><a class="bouton" href="/serie/<?= e($serieDefaut) ?>/classements">Classements et calendrier</a> &nbsp; <a class="bouton clair" href="<?= e($lienCapitaine) ?>">Espace capitaine</a></p>
  <div class="chiffres">
    <?php foreach (['golfs' => 'Golfs membres', 'equipes' => 'Équipes engagées', 'journees' => 'Journées de championnat', 'competitions' => 'Compétitions individuelles'] as $cle => $libelle): ?>
      <?php if ($chiffres[$cle] > 0): ?><div><b><?= e($chiffres[$cle]) ?></b><span><?= e($libelle) ?></span></div><?php endif; ?>
    <?php endforeach; ?>
  </div>
</div></section>

<div class="citation"><div class="conteneur"><span><?= e($brut($pages['citation'], '« Le sport relie les gens, construit des amitiés et avant tout, vous permet de vous faire plaisir. »')) ?></span><a href="https://www.ffgolf.org/" target="_blank" rel="noopener">Fédération française de golf →</a></div></div>

<section class="bloc"><div class="conteneur esprit">
  <div>
    <span class="etiquette">« Les Coupes de l'Amitié »</span>
    <h2><?= e($titrePage($pages['accueil-esprit'], 'Découvrir de nouveaux parcours, et de nouveaux amis')) ?></h2>
    <div class="lead"><?= $corps($pages['accueil-esprit'], '<p>Nous organisons des rencontres interclubs par matchs aller-retour, ainsi que des compétitions individuelles réservées à nos membres. Les matchs se jouent dans un esprit sportif, dans le respect des règles de Saint Andrews, et se finissent toujours de façon conviviale autour d\'un bon repas.</p>') ?></div>
  </div>
  <aside class="encart">
    <h3><?= e($titrePage($pages['accueil-encart'], 'Une saison à l\'AGCA')) ?></h3><div class="filet"></div>
    <?= $corps($pages['accueil-encart'], '<ul class="points">
      <li><b>Des parcours variés</b>, de Gap à Sainte-Maxime, à découvrir en équipe.</li>
      <li><b>Une saison complète</b>, le samedi, en poules de quatre équipes, aller et retour.</li>
      <li><b>Cinq rendez-vous individuels</b> réservés aux joueurs des clubs membres.</li>
    </ul>
    <p class="invitation">Votre club souhaite engager une équipe ? <a href="/contact">Écrivez-nous</a>.</p>') ?>
  </aside>
</div></section>

<section class="bloc beige"><div class="conteneur">
  <div class="titre-section"><div><span class="etiquette">Comment ça se joue</span><h2>Les formats</h2></div></div>
  <div class="grille-4">
    <?php
    $formats = [
        ['format-interclubs', 'Inter-clubs par équipe', '<p>De septembre à mai, pour entraîner joueurs et joueuses en basse saison et les préparer aux championnats fédéraux de France, de Ligue et aux Grands Prix. Divisions généralement de 4 équipes, rencontres le samedi en aller-retour, soit 6 rencontres par an.</p>'],
        ['format-mixte', 'Amitié 2e série mixte', '<p>Équipes de 10 joueurs, dames et hommes. Matchs en net : 9 trous en double et 9 trous en simple. Index compris entre 11,5 et 22, plus un joker homme et une joker dame possibles avec un index inférieur.</p>'],
        ['format-h1', 'Amitié Homme 1re série', '<p>Équipes de joueurs classés par ordre des index. Matchs en brut : 1 double et 4 simples sur 18 trous. Index non limités.</p>'],
        ['format-individuelles', 'Compétitions individuelles', '<p>Master, Challenge, Trophée, Tournoi et Coupe des Capitaines : réservées aux joueurs des clubs membres ayant participé au moins une fois à une rencontre par équipes. <a href="#competitions">Les cinq rendez-vous</a></p>'],
    ];
    ?>
    <?php foreach ($formats as $i => [$slug, $titreRepli, $corpsRepli]): ?>
    <div class="format"><span class="numero"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span><h3><?= e($titrePage($pages[$slug], $titreRepli)) ?></h3><?= $corps($pages[$slug], $corpsRepli) ?></div>
    <?php endforeach; ?>
  </div>
</div></section>

<?php if ($prochaine_journee !== null): ?>
<div class="prochaine"><div class="conteneur">
  <span class="etiquette">Prochaine journée</span>
  <strong><?= e($dateLongue($prochaine_journee['date'])) ?></strong>
  <?php foreach ($prochaine_journee['series'] as $sj): ?>
    <?php
    $detail = $sj['libelle'];
    // Numéro de journée affiché seulement si la date ne porte qu'une journée pour la série
    // (un report peut faire cohabiter deux journées le même jour).
    if ($sj['nb_journees'] === 1 && $sj['journee_numero'] !== null && $sj['journee_phase'] !== null) {
        $detail .= ' — ' . $rang((int) $sj['journee_numero']) . ' journée ' . $sj['journee_phase'] . ',';
    } else {
        $detail .= ' —';
    }
    $detail .= ' ' . $sj['nb_rencontres'] . ' rencontre' . ($sj['nb_rencontres'] > 1 ? 's' : '');
    $detail .= ' dans ' . $sj['nb_divisions'] . ' poule' . ($sj['nb_divisions'] > 1 ? 's' : '');
    ?>
    <span><?= e($detail) ?></span>
  <?php endforeach; ?>
  <a class="lien-fleche" href="/serie/<?= e($prochaine_journee['series'][0]['code'] ?? $serieDefaut) ?>/suivi">Voir le calendrier</a>
</div></div>
<?php endif; ?>

<section class="bloc"><div class="conteneur deux-colonnes">
  <div>
    <?php foreach ($classements as $i => $c): ?>
    <div class="titre-section"><div><?php if ($i === 0): ?><span class="etiquette">Classements en cours</span><?php endif; ?><h2><?= e($c['serie']['libelle']) ?></h2></div><a class="lien-fleche" href="/serie/<?= e($c['serie']['code']) ?>/classements"><?= $i === 0 ? 'Toutes les divisions' : 'Classement' ?></a></div>
    <div class="carte"><div class="carte-tete"><span><?= e($c['division']['libelle']) ?></span><a href="/serie/<?= e($c['serie']['code']) ?>/classements">Saison <?= e($saison['libelle'] ?? '') ?></a></div>
    <table><thead><tr><th>#</th><th>Équipe</th><th class="num">Joués</th><th class="num">Diff.</th><th class="num">Points</th></tr></thead><tbody>
      <?php foreach ($c['lignes'] as $l): ?>
      <tr><td class="rang"><?= e($l['rang']) ?></td><td><?= e($l['nom']) ?></td><td class="num"><?= e($l['joues']) ?></td><td class="num"><?= e($l['diff']) ?></td><td class="num"><b><?= e(fmt_pts($l['points'])) ?></b></td></tr>
      <?php endforeach; ?>
    </tbody></table></div>
    <?php endforeach; ?>
    <?php if ($classements === []): ?>
    <div class="titre-section"><div><span class="etiquette">Classements en cours</span><h2>Championnats interclubs</h2></div><a class="lien-fleche" href="/serie/<?= e($serieDefaut) ?>/classements">Toutes les divisions</a></div>
    <p class="aide">Les classements seront publiés dès la première journée de la saison.</p>
    <?php endif; ?>
  </div>
  <div>
    <div class="titre-section"><div><span class="etiquette">Calendrier</span><h2>Prochaines rencontres</h2></div></div>
    <?php if ($prochaines_rencontres !== []): ?>
    <ul class="liste-rencontres">
      <?php foreach ($prochaines_rencontres as $r): ?>
      <?php $d = $jour((string) $r['date_reelle']); ?>
      <li><div class="date"><?= $d === null ? e($r['date_reelle']) : e($d->format('j') . ' ' . $moisCourts[(int) $d->format('n') - 1]) ?><small><?= $d === null ? '' : e($joursLongs[(int) $d->format('w')]) ?></small></div><div><?= e($r['recevant_nom']) ?> reçoit <?= e($r['invite_nom']) ?><div class="div"><?= e($libelleSerie[(string) $r['serie_code']] ?? $r['serie_code']) ?> · <?= e($r['division_libelle']) ?></div></div></li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p class="aide">Aucune rencontre à venir au calendrier.</p>
    <?php endif; ?>
    <p class="lien-calendrier"><a class="lien-fleche" href="/serie/<?= e($serieDefaut) ?>/suivi">Calendrier complet</a></p>
  </div>
</div></section>

<?php /* Aucun repli codé en dur : comme les actualités, la section n'est rendue que si des compétitions existent. */ ?>
<?php if ($competitions !== []): ?>
<section class="bloc beige" id="competitions"><div class="conteneur">
  <div class="titre-section"><div><span class="etiquette">Nos compétitions</span><h2>Cinq rendez-vous dans la saison</h2></div><a class="lien-fleche" href="/competitions">Résultats et palmarès</a></div>
  <div class="grille-5">
    <?php foreach ($competitions as $c): ?>
    <div class="carte-comp">
      <?php if (trim((string) $c['accroche']) !== ''): ?><span class="etiquette"><?= e($c['accroche']) ?></span><?php endif; ?>
      <h3><a href="/competitions/<?= e($c['code']) ?>"><?= e($c['nom']) ?></a></h3>
      <p class="formule"><?= e($c['formule']) ?></p>
      <?php $p = $c['dernier_palmares']; ?>
      <?php if ($p !== null): ?>
        <?php
        $aVainqueur = trim((string) $p['vainqueur']) !== '';
        $etiquettePal = $aVainqueur ? 'Vainqueur ' . $p['saison'] : 'Dernière édition';
        $valeurPal = $aVainqueur ? (string) $p['vainqueur'] : (string) $p['saison'];
        if (trim((string) $p['lieu']) !== '') { $valeurPal .= ' à ' . $p['lieu']; }
        ?>
      <p class="vainqueur"><?= e($etiquettePal) ?><b><?= e($valeurPal) ?></b></p>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div></section>
<?php endif; ?>

<?php /* Aucun repli ici : les articles de la maquette sont des exemples, pas du contenu du site. */ ?>
<?php if ($actualites !== []): ?>
<section class="bloc"><div class="conteneur">
  <div class="titre-section"><div><span class="etiquette">Actualités</span><h2>La vie de l'association</h2></div><a class="lien-fleche" href="/actualites">Toutes les actualités</a></div>
  <div class="grille-3">
    <?php foreach ($actualites as $a): ?>
    <?php $d = $jour((string) $a['date_publication']); ?>
    <article class="actu">
      <time datetime="<?= e(substr((string) $a['date_publication'], 0, 10)) ?>"><?= $d === null ? e($a['date_publication']) : e(mb_convert_case($moisLongs[(int) $d->format('n') - 1], MB_CASE_TITLE, 'UTF-8') . ' ' . $d->format('Y')) ?></time>
      <h3><a href="/actualites/<?= e($a['id']) ?>-<?= e($a['slug']) ?>"><?= e($a['titre']) ?></a></h3>
      <?php if (trim((string) $a['resume']) !== ''): ?><p><?= e($a['resume']) ?></p><?php endif; ?>
    </article>
    <?php endforeach; ?>
  </div>
</div></section>
<?php endif; ?>

<?php /* Aucun repli codé en dur : comme les actualités, la section n'est rendue que si des golfs membres existent.
     Sans la section Actualités, les Golfs enchaînent sur un bloc beige : on garde l'alternance. */ ?>
<?php if ($golfs !== []): ?>
<section class="bloc<?= $actualites === [] ? '' : ' beige' ?>" id="golfs"><div class="conteneur">
  <div class="titre-section"><div><span class="etiquette">Golfs membres</span><h2>De Gap à Sainte-Maxime</h2></div><a class="lien-fleche" href="/golfs">Tous les golfs</a></div>
  <div class="grille-golfs">
    <?php foreach ($golfs as $g): ?>
    <div class="golf"><b><?= e($g['nom']) ?></b><span><?= e($g['ville']) ?></span></div>
    <?php endforeach; ?>
  </div>
</div></section>
<?php endif; ?>
