<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Championnat<?= $saison ? ' · Saison ' . e($saison['libelle']) : '' ?></span>
  <h1><?= e($serie['libelle']) ?></h1>
  <?= $vue->inclure('public/partials/selecteur_saison', compact('serie', 'saison', 'saisons') + ['actif' => 'classements']) ?>
</div></div>
<section class="bloc"><div class="conteneur">
  <?php if ($divisions === []): ?><p>Aucune division pour cette saison.</p><?php endif; ?>
  <?php foreach ($divisions as $d): ?>
  <div class="division">
    <h2><?= e($d['libelle']) ?> <small><?= count($d['classement']) ?> équipes</small></h2>
    <div class="carte tableau-defilant"><table class="classement">
      <thead><tr><th>Rang</th><th>Golf</th><th class="num">Points</th><th class="num">Bonus</th><th class="num">Joués</th><th class="num">G</th><th class="num">N</th><th class="num">P</th><th class="num">Forf.</th><th class="num">Pour</th><th class="num">Contre</th><th class="num">Diff</th></tr></thead>
      <tbody>
      <?php foreach ($d['classement'] as $l): ?>
        <tr><td class="rang"><?= e($l['rang']) ?></td><td><?= e($l['nom']) ?></td><td class="num"><b><?= e(fmt_pts($l['points'])) ?></b></td><td class="num"><?= e(fmt_pts($l['bonus'])) ?></td><td class="num"><?= e($l['joues']) ?></td><td class="num"><?= e($l['gagnes']) ?></td><td class="num"><?= e($l['nuls']) ?></td><td class="num"><?= e($l['perdus']) ?></td><td class="num"><?= e($l['forfaits']) ?></td><td class="num"><?= e($l['pour']) ?></td><td class="num"><?= e($l['contre']) ?></td><td class="num"><?= e($l['diff'] > 0 ? '+' . $l['diff'] : $l['diff']) ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
  <?php endforeach; ?>
  <p class="aide">Points de rencontre : gagnée 3, nulle 2, perdue 1, forfait 0. Bonus : victoire à l'extérieur +1, nul à l'extérieur +0,5. Départage : confrontation directe, puis différence pour − contre.</p>
</div></section>
