<?php
/** Grille des golfs membres : nom, ville, site web. */
/** Adresse http(s) sûre à partir de la saisie de l'admin (« www.golf.fr » ou « https://… »), sinon null. */
$lienSite = static function (?string $url): ?string {
    $url = trim((string) $url);
    if ($url === '') { return null; }
    if (preg_match('#^https?://#i', $url) === 1) { return $url; }
    return preg_match('~^[\w.-]+\.[a-z]{2,}(?:[/?].*)?$~i', $url) === 1 ? 'https://' . $url : null;
};
?>
<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Golfs membres</span>
  <h1>De Gap à Sainte-Maxime</h1>
</div></div>
<section class="bloc"><div class="conteneur">
  <?php if ($golfs === []): ?>
  <p class="aide">La liste des golfs membres sera publiée prochainement.</p>
  <?php else: ?>
  <div class="grille-golfs">
    <?php foreach ($golfs as $g): ?>
    <?php $lien = $lienSite($g['site_web']); ?>
    <div class="golf">
      <b><?= e($g['nom']) ?></b>
      <?php if (trim((string) $g['ville']) !== ''): ?><span><?= e($g['ville']) ?></span><?php endif; ?>
      <?php if ($lien !== null): ?><span><a href="<?= e($lien) ?>" target="_blank" rel="noopener"><?= e(preg_replace('~^www\.~i', '', (string) parse_url($lien, PHP_URL_HOST))) ?></a></span><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></section>
