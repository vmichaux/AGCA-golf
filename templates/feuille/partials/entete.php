<div class="entete-page"><div class="conteneur">
  <span class="etiquette"><?= e($rencontre['division_libelle']) ?> · <?= e(ucfirst($rencontre['journee_phase']) . ' journée ' . $rencontre['journee_numero']) ?> · prévue le <?= e(fmt_date($rencontre['date_calendrier'])) ?></span>
  <h1><?= e($titre_page ?? 'Feuille de match') ?></h1>
  <p class="equipes"><span><?= e($rencontre['recevant_nom']) ?></span> <em>reçoit</em> <span><?= e($rencontre['invite_nom']) ?></span></p>
  <?php if ($rencontre['reportee']): ?><p class="report">Reportée au <?= e(fmt_date($rencontre['date_reelle'])) ?></p><?php endif; ?>
  <?php if ($rencontre['statut'] !== 'a_jouer'): ?>
    <p class="totaux">Parties : <strong><?= e($rencontre['total_pour']) ?> - <?= e($rencontre['total_contre']) ?></strong> ·
      Points de rencontre : <strong><?= e(fmt_pts($rencontre['pts_rencontre_pour'])) ?> - <?= e(fmt_pts($rencontre['pts_rencontre_contre'])) ?></strong><?= (float) $rencontre['bonus_invite'] > 0 ? ' (+' . e(fmt_pts($rencontre['bonus_invite'])) . ' bonus invité)' : '' ?>
      <?= $rencontre['statut'] === 'forfait' ? ' · <span class="pastille forfait">Forfait</span>' : '' ?></p>
    <?php $alertes = $rencontre['alertes'] ? json_decode($rencontre['alertes'], true) : []; if ($alertes !== []): ?>
      <ul class="alertes"><?php foreach ($alertes as $c): ?><li><span class="pastille">!</span> <?= e(\Agca\Domain\Controles::LIBELLES[$c] ?? $c) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
  <?php endif; ?>
</div></div>
