<?php
/**
 * Fiche d'une compétition : présentation puis palmarès, de la saison la plus récente à la plus ancienne.
 * `corps_html` et `detail_html` sont produits par Agca\Domain\Markdown : seuls échos sans e().
 */
?>
<div class="entete-page"><div class="conteneur">
  <?php if (trim((string) $competition['accroche']) !== ''): ?><span class="etiquette"><?= e($competition['accroche']) ?></span><?php endif; ?>
  <h1><?= e($competition['nom']) ?></h1>
  <?php if (trim((string) $competition['formule']) !== ''): ?><p class="sous-titre"><?= e($competition['formule']) ?></p><?php endif; ?>
  <nav class="sous-nav" aria-label="Compétitions">
    <?php foreach ($competitions as $c): ?><a<?= $c['code'] === $competition['code'] ? ' class="actif"' : '' ?> href="/competitions/<?= e($c['code']) ?>"><?= e($c['nom']) ?></a><?php endforeach; ?>
  </nav>
</div></div>
<?php if (trim((string) $competition['corps_html']) !== ''): ?>
<section class="bloc"><div class="conteneur">
  <div class="presentation"><?= $competition['corps_html'] ?></div>
</div></section>
<?php endif; ?>
<section class="bloc<?= trim((string) $competition['corps_html']) === '' ? '' : ' beige' ?>"><div class="conteneur">
  <h2>Palmarès</h2>
  <?php if ($palmares === []): ?>
  <p class="aide">Le palmarès de cette compétition sera publié prochainement.</p>
  <?php endif; ?>
  <?php foreach ($palmares as $p): ?>
  <div class="carte">
    <div class="carte-tete">
      <span><?= e($p['saison']) ?><?= trim((string) $p['lieu']) === '' ? '' : ' · ' . e($p['lieu']) ?></span>
      <?php if ($p['document_fichier'] !== null): ?>
      <a href="/documents/<?= e($p['document_fichier']) ?>"><?= e($p['document_titre']) ?></a>
      <?php endif; ?>
    </div>
    <div class="carte-corps">
      <?php if (trim((string) $p['vainqueur']) !== ''): ?><p><strong>Vainqueur :</strong> <?= e($p['vainqueur']) ?></p><?php endif; ?>
      <?php if (trim((string) $p['detail_html']) !== ''): ?><div class="presentation"><?= $p['detail_html'] ?></div><?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div></section>
