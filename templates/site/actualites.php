<?php
/** Liste des actualités publiées, dix par page. */
$moisLongs = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$moisAnnee = static function (string $ymd) use ($moisLongs): string {
    $d = \DateTimeImmutable::createFromFormat('Y-m-d', substr($ymd, 0, 10));
    return $d === false ? $ymd : mb_convert_case($moisLongs[(int) $d->format('n') - 1], MB_CASE_TITLE, 'UTF-8') . ' ' . $d->format('Y');
};
$lienPage = static fn(int $n): string => $n <= 1 ? '/actualites' : '/actualites?page=' . $n;
?>
<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Actualités</span>
  <h1>La vie de l'association</h1>
</div></div>
<section class="bloc"><div class="conteneur">
  <?php if ($actualites === []): ?>
  <p class="aide">Aucune actualité publiée pour le moment.</p>
  <?php else: ?>
  <div class="grille-3">
    <?php foreach ($actualites as $a): ?>
    <article class="actu">
      <time datetime="<?= e(substr((string) $a['date_publication'], 0, 10)) ?>"><?= e($moisAnnee((string) $a['date_publication'])) ?></time>
      <h3><a href="/actualites/<?= e($a['id']) ?>-<?= e($a['slug']) ?>"><?= e($a['titre']) ?></a></h3>
      <?php if (trim((string) $a['resume']) !== ''): ?><p><?= e($a['resume']) ?></p><?php endif; ?>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if ($nb_pages > 1): ?>
  <nav class="actions" aria-label="Pages d'actualités">
    <?php if ($page > 1): ?><a class="bouton contour" href="<?= e($lienPage($page - 1)) ?>">Plus récentes</a><?php endif; ?>
    <?php if ($page < $nb_pages): ?><a class="bouton contour" href="<?= e($lienPage($page + 1)) ?>">Plus anciennes</a><?php endif; ?>
    <span class="aide">Page <?= e($page) ?> sur <?= e($nb_pages) ?></span>
  </nav>
  <?php endif; ?>
  <?php endif; ?>
</div></section>
