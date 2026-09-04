<?php
/** Les cinq compétitions individuelles : mêmes cartes que sur l'accueil. */
?>
<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Nos compétitions</span>
  <h1>Compétitions individuelles</h1>
  <?php if ($competitions !== []): ?>
  <nav class="sous-nav" aria-label="Compétitions">
    <?php foreach ($competitions as $c): ?><a href="/competitions/<?= e($c['code']) ?>"><?= e($c['nom']) ?></a><?php endforeach; ?>
  </nav>
  <?php endif; ?>
</div></div>
<section class="bloc"><div class="conteneur">
  <?php if ($competitions === []): ?>
  <p class="aide">Les compétitions seront publiées prochainement.</p>
  <?php else: ?>
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
  <?php endif; ?>
</div></section>
