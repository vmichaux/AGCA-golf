<h1>Feuille de match</h1>
<?= $vue->inclure('feuille/partials/entete', ['rencontre' => $rencontre]) ?>
<?php if ($rencontre['statut'] === 'a_jouer'): ?>
  <p>Feuille non encore saisie.</p>
<?php else: ?>
<div class="tableau-defilant"><table class="feuille">
  <thead><tr><th>#</th><th>Recevant</th><th class="num">Index</th><th class="num">Pts</th><th>Score</th><th class="num">Pts</th><th>Invité</th><th class="num">Index</th></tr></thead>
  <tbody>
  <?php foreach ($parties as $p): $s = $p['score_as'] ? 'AS' : ($p['score_trous'] === null ? '' : ($p['score_restants'] ? $p['score_trous'] . '&' . $p['score_restants'] : $p['score_trous'] . ' UP')); ?>
    <tr class="<?= $p['type'] === 'double' ? 'double' : '' ?>">
      <td><?= e($p['numero']) ?><?= $p['type'] === 'double' ? ' D' : '' ?></td>
      <td><?= e($p['rec_nom1']) ?><?= $p['rec_sexe1'] ? ' (' . e($p['rec_sexe1']) . ')' : '' ?><?= $p['rec_nom2'] ? '<br>' . e($p['rec_nom2']) . ($p['rec_sexe2'] ? ' (' . e($p['rec_sexe2']) . ')' : '') : '' ?></td>
      <td class="num"><?= e($p['rec_index1']) ?><?= $p['rec_index2'] !== null ? '<br>' . e($p['rec_index2']) : '' ?></td>
      <td class="num"><?= e($p['pts_pour']) ?></td>
      <td><?= e($s) ?><?= $p['resultat'] ? ' <span class="aide">(' . e(['G' => 'gagné', 'N' => 'nul', 'P' => 'perdu'][$p['resultat']]) . ' recevant)</span>' : '' ?></td>
      <td class="num"><?= e($p['pts_contre']) ?></td>
      <td><?= e($p['inv_nom1']) ?><?= $p['inv_sexe1'] ? ' (' . e($p['inv_sexe1']) . ')' : '' ?><?= $p['inv_nom2'] ? '<br>' . e($p['inv_nom2']) . ($p['inv_sexe2'] ? ' (' . e($p['inv_sexe2']) . ')' : '') : '' ?></td>
      <td class="num"><?= e($p['inv_index1']) ?><?= $p['inv_index2'] !== null ? '<br>' . e($p['inv_index2']) : '' ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<p>
  <?php if ($peutSaisir): ?><a class="bouton" href="/rencontre/<?= e($rencontre['id']) ?>/saisie"><?= $rencontre['statut'] === 'a_jouer' ? 'Saisir la feuille' : 'Corriger la feuille' ?></a><?php endif; ?>
  <a class="bouton bouton-secondaire" href="<?= !empty($utilisateur['est_admin']) ? '/admin' : '/capitaine' ?>">Retour</a>
</p>
<?php if ($journal !== []): ?>
  <h2>Historique</h2>
  <ul class="journal"><?php foreach ($journal as $l): ?><li><?= e($l['quand']) ?> — <?= e($l['identifiant'] ?? 'système') ?> — <?= e($l['action']) ?><?= $l['detail'] && $l['detail'] !== '[]' ? ' <span class="aide">' . e($l['detail']) . '</span>' : '' ?></li><?php endforeach; ?></ul>
<?php endif; ?>
