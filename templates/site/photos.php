<?php
/** Albums photo : chaque album renvoie vers sa galerie (dossiers `images…/` conservés sur le serveur). */
/** Adresse sûre : http(s) ou chemin absolu du serveur ; sinon null (album affiché sans lien). */
$lienAlbum = static function (?string $url): ?string {
    $url = trim((string) $url);
    if ($url === '') { return null; }
    return preg_match('#^(?:https?://|/)[^\s"<>]*$#i', $url) === 1 ? $url : null;
};
?>
<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Albums</span>
  <h1>Photos</h1>
</div></div>
<section class="bloc"><div class="conteneur">
  <?php if ($albums === []): ?>
  <p class="aide">Les albums photo seront publiés prochainement.</p>
  <?php else: ?>
  <ul class="cartes">
    <?php foreach ($albums as $a): ?>
    <?php $lien = $lienAlbum($a['url']); ?>
    <?php /* Les galeries sortent de l'application (dossiers statiques, sites tiers) : nouvel onglet. */ ?>
    <li><a <?= $lien === null ? '' : 'href="' . e($lien) . '" target="_blank" rel="noopener"' ?>><strong><?= e($a['titre']) ?></strong><?php if ($a['annee'] !== null): ?><span><?= e($a['annee']) ?></span><?php endif; ?></a></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div></section>
