<div class="entete-feuille">
  <p><strong><?= e($rencontre['division_libelle']) ?></strong> · <?= e(ucfirst($rencontre['journee_phase']) . ' journée ' . $rencontre['journee_numero']) ?> · prévue le <?= e(fmt_date($rencontre['date_calendrier'])) ?><?= $rencontre['reportee'] ? ' · <span class="report">reportée au ' . e(fmt_date($rencontre['date_reelle'])) . '</span>' : '' ?></p>
  <p class="equipes"><span><?= e($rencontre['recevant_nom']) ?></span> <em>reçoit</em> <span><?= e($rencontre['invite_nom']) ?></span></p>
  <?php if ($rencontre['statut'] !== 'a_jouer'): ?>
    <p class="totaux">Parties : <strong><?= e($rencontre['total_pour']) ?> - <?= e($rencontre['total_contre']) ?></strong> ·
      Points de rencontre : <strong><?= e(fmt_pts($rencontre['pts_rencontre_pour'])) ?> - <?= e(fmt_pts($rencontre['pts_rencontre_contre'])) ?></strong><?= (float) $rencontre['bonus_invite'] > 0 ? ' (+' . e(fmt_pts($rencontre['bonus_invite'])) . ' bonus invité)' : '' ?>
      <?= $rencontre['statut'] === 'forfait' ? ' · <span class="pastille pastille-forfait">Forfait</span>' : '' ?></p>
    <?php $alertes = $rencontre['alertes'] ? json_decode($rencontre['alertes'], true) : []; if ($alertes !== []): ?>
      <ul class="alertes"><?php foreach ($alertes as $c): ?><li><span class="pastille">!</span> <?= e(\Agca\Domain\Controles::LIBELLES[$c] ?? $c) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
  <?php endif; ?>
</div>
