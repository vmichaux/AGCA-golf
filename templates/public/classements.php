<h1>Classements — <?= e($serie['libelle']) ?><?= $saison ? ' — ' . e($saison['libelle']) : '' ?></h1>
<?= $vue->inclure('public/partials/selecteur_saison', compact('serie', 'saison', 'saisons')) ?>
<?php if ($divisions === []): ?><p>Aucune division pour cette saison.</p><?php endif; ?>
<?php foreach ($divisions as $d): ?>
  <h2><?= e($d['libelle']) ?></h2>
  <div class="tableau-defilant"><table class="classement">
    <thead><tr><th>Rang</th><th>Golf</th><th class="num">Points</th><th class="num">Bonus</th><th class="num">Joués</th><th class="num">G</th><th class="num">N</th><th class="num">P</th><th class="num">Forf.</th><th class="num">Pour</th><th class="num">Contre</th><th class="num">Diff</th></tr></thead>
    <tbody>
    <?php foreach ($d['classement'] as $l): ?>
      <tr><td class="num"><?= e($l['rang']) ?></td><td><?= e($l['nom']) ?></td><td class="num"><?= e(fmt_pts($l['points'])) ?></td><td class="num"><?= e(fmt_pts($l['bonus'])) ?></td><td class="num"><?= e($l['joues']) ?></td><td class="num"><?= e($l['gagnes']) ?></td><td class="num"><?= e($l['nuls']) ?></td><td class="num"><?= e($l['perdus']) ?></td><td class="num"><?= e($l['forfaits']) ?></td><td class="num"><?= e($l['pour']) ?></td><td class="num"><?= e($l['contre']) ?></td><td class="num"><?= e($l['diff'] > 0 ? '+' . $l['diff'] : $l['diff']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
<?php endforeach; ?>
<p class="aide">Points de rencontre : gagnée 3, nulle 2, perdue 1, forfait 0. Bonus : victoire à l'extérieur +1, nul à l'extérieur +0,5. Départage : confrontation directe, puis différence pour − contre.</p>
