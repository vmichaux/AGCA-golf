<?php
/** Article : `corps_html` est produit par Agca\Domain\Markdown, seul écho sans e(). */
?>
<div class="entete-page"><div class="conteneur">
  <span class="etiquette"><?= e(fmt_date((string) $actualite['date_publication'])) ?></span>
  <h1><?= e($actualite['titre']) ?></h1>
  <?php if (trim((string) $actualite['resume']) !== ''): ?><p class="sous-titre"><?= e($actualite['resume']) ?></p><?php endif; ?>
  <nav class="sous-nav" aria-label="Actualités"><a href="/actualites">Toutes les actualités</a></nav>
</div></div>
<section class="bloc"><div class="conteneur">
  <div class="presentation"><?= $actualite['corps_html'] ?></div>
</div></section>
