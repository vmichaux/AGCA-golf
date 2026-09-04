<?php
/**
 * Page de texte (statuts, assemblées générales, voyage, mentions légales, page libre).
 * `corps_html` est produit par Agca\Domain\Markdown : seul écho sans e().
 */
$ko = static fn(int $octets): int => max(1, (int) round($octets / 1024));
$extension = static fn(string $fichier): string => mb_strtoupper(pathinfo($fichier, PATHINFO_EXTENSION));
?>
<div class="entete-page"><div class="conteneur">
  <h1><?= e($page['titre']) ?></h1>
  <?= $vue->inclure('site/partials/nav_association', ['actif' => '/page/' . $page['slug']]) ?>
</div></div>
<section class="bloc"><div class="conteneur">
  <div class="presentation"><?= $page['corps_html'] ?></div>
  <?php if ($documents !== []): ?>
  <h2>Documents</h2>
  <ul class="cartes">
    <?php foreach ($documents as $d): ?>
    <li><a href="/documents/<?= e($d['fichier']) ?>"><strong><?= e($d['titre']) ?></strong><span><?= e($extension((string) $d['fichier'])) ?> · <?= e($ko((int) $d['taille'])) ?> Ko</span></a></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div></section>
