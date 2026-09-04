<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Championnat<?= $saison ? ' · Saison ' . e($saison['libelle']) : '' ?></span>
  <h1><?= e($serie['libelle']) ?></h1>
  <?= $vue->inclure('public/partials/selecteur_saison', compact('serie', 'saison', 'saisons') + ['actif' => 'suivi']) ?>
</div></div>
<section class="bloc"><div class="conteneur">
  <?php if ($divisions === []): ?><p>Aucune division pour cette saison.</p><?php endif; ?>
  <?php foreach ($divisions as $d): ?>
  <div class="division">
    <div class="titre-section"><div><span class="etiquette">Calendrier et suivi</span><h2><?= e($d['libelle']) ?></h2></div></div>
    <div class="carte tableau-defilant"><table class="suivi">
      <thead><tr><th>Recevant</th><th>Invité</th><th>Date du match</th><th class="num">Résultat</th><th><span class="visuellement-cache">Feuille</span></th></tr></thead>
      <tbody>
      <?php $precedente = null; foreach ($d['rencontres'] as $r): $cle = $r['journee_phase'] . $r['journee_numero']; ?>
        <?php if ($cle !== $precedente): ?>
        <tr class="journee-tete"><td colspan="5"><?= e(ucfirst($r['journee_phase']) . ' · journée ' . $r['journee_numero'] . ' · ' . fmt_date($r['date_calendrier'])) ?></td></tr>
        <?php endif; ?>
        <tr>
          <td><?= e($r['recevant_nom']) ?></td>
          <td><?= e($r['invite_nom']) ?></td>
          <td><?= $r['reportee'] ? '<span class="report">Reporté au ' . e(fmt_date($r['date_reelle'])) . '</span>' : e(fmt_date($r['date_reelle'])) ?></td>
          <td class="num"><?php if ($r['statut'] === 'a_jouer'): ?>–<?php else: ?><b><?= e($r['total_pour']) ?> - <?= e($r['total_contre']) ?></b><?php endif; ?></td>
          <td>
            <?php if ($r['statut'] === 'forfait'): ?><span class="pastille forfait" title="Forfait">F</span><?php endif; ?>
            <?php $alertes = $r['alertes'] ? json_decode($r['alertes'], true) : []; if ($alertes !== []): ?><span class="pastille" title="<?= e(implode(', ', array_map(fn($c) => \Agca\Domain\Controles::LIBELLES[$c] ?? $c, $alertes))) ?>"><?= count($alertes) ?></span><?php endif; ?>
            <?php if (!empty($utilisateur) && $r['statut'] !== 'a_jouer' && ($utilisateur['est_admin'] || in_array((int) $utilisateur['equipe_id'], [(int) $r['recevant_id'], (int) $r['invite_id']], true))): ?><a href="/rencontre/<?= e($r['id']) ?>">feuille</a><?php endif; ?>
          </td>
        </tr>
      <?php $precedente = $cle; endforeach; ?>
      </tbody></table></div>
  </div>
  <?php endforeach; ?>
  <p class="aide">Une pastille ocre signale une feuille à vérifier par l'administrateur (index, jokers, feuille incomplète). F : forfait.</p>
</div></section>
