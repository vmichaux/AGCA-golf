<?= $vue->inclure('feuille/partials/entete', ['rencontre' => $rencontre, 'titre_page' => 'Feuille de match']) ?>
<section class="bloc"><div class="conteneur">
<?php if ($rencontre['statut'] === 'a_jouer'): ?>
  <p>Feuille non encore saisie.</p>
<?php else: ?>
<div class="carte tableau-defilant"><table class="feuille">
  <thead><tr><th>#</th><th>Recevant</th><th class="num">Index</th><th class="num">Pts</th><th>Score</th><th class="num">Pts</th><th>Invité</th><th class="num">Index</th></tr></thead>
  <tbody>
  <?php foreach ($parties as $p): $s = \Agca\Domain\Score::texteDepuisColonnes($p['score_trous'], $p['score_restants'], $p['score_as']); ?>
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
<?php if ($peutModifierDate && empty($utilisateur['est_admin'])): ?>
  <form method="post" action="/rencontre/<?= e($rencontre['id']) ?>/date" class="panneau">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label for="date_reelle">Report : nouvelle date convenue avec l'adversaire</label>
    <input id="date_reelle" type="date" name="date_reelle" value="<?= e($rencontre['date_reelle']) ?>" class="champ-date">
    <p class="actions"><button class="bouton bouton-secondaire">Enregistrer la date</button></p>
  </form>
<?php endif; ?>
<?php if (!empty($utilisateur['est_admin']) && $rencontre['saison_statut'] === 'active'): ?><?= $vue->inclure('feuille/partials/actions_admin', ['rencontre' => $rencontre, 'csrf' => $csrf]) ?><?php endif; ?>
<p class="actions">
  <?php if ($peutSaisir): ?><a class="bouton" href="/rencontre/<?= e($rencontre['id']) ?>/saisie"><?= $rencontre['statut'] === 'a_jouer' ? 'Saisir la feuille' : 'Corriger la feuille' ?></a><?php endif; ?>
  <a class="bouton bouton-secondaire" href="<?= !empty($utilisateur['est_admin']) ? '/admin' : '/capitaine' ?>">Retour</a>
</p>
<?php if ($journal !== []): ?>
  <h2>Historique</h2>
  <ul class="journal"><?php foreach ($journal as $l): ?><li><?= e($l['quand']) ?> — <?= e($l['identifiant'] ?? 'système') ?> — <?= e($l['action']) ?><?= $l['detail'] && $l['detail'] !== '[]' ? ' <span class="aide">' . e($l['detail']) . '</span>' : '' ?></li><?php endforeach; ?></ul>
<?php endif; ?>
</div></section>
