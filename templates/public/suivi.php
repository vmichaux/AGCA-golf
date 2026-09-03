<h1>Calendrier et suivi — <?= e($serie['libelle']) ?><?= $saison ? ' — ' . e($saison['libelle']) : '' ?></h1>
<?= $vue->inclure('public/partials/selecteur_saison', compact('serie', 'saison', 'saisons')) ?>
<?php foreach ($divisions as $d): ?>
  <h2><?= e($d['libelle']) ?></h2>
  <div class="tableau-defilant"><table class="suivi">
    <thead><tr><th>Journée</th><th>Date calendrier</th><th>Recevant</th><th>Invité</th><th>Date du match</th><th class="num">Résultat</th><th></th></tr></thead>
    <tbody>
    <?php $precedente = null; foreach ($d['rencontres'] as $r): $cle = $r['journee_phase'] . $r['journee_numero']; ?>
      <tr<?= $cle !== $precedente ? ' class="nouvelle-journee"' : '' ?>>
        <td><?= $cle !== $precedente ? e(ucfirst($r['journee_phase']) . ' J' . $r['journee_numero']) : '' ?></td>
        <td><?= $cle !== $precedente ? e(fmt_date($r['date_calendrier'])) : '' ?></td>
        <td><?= e($r['recevant_nom']) ?></td>
        <td><?= e($r['invite_nom']) ?></td>
        <td><?= $r['reportee'] ? '<span class="report">Reporté au ' . e(fmt_date($r['date_reelle'])) . '</span>' : e(fmt_date($r['date_reelle'])) ?></td>
        <td class="num"><?php if ($r['statut'] === 'a_jouer'): ?>–<?php else: ?><?= e($r['total_pour']) ?> - <?= e($r['total_contre']) ?><?php endif; ?></td>
        <td>
          <?php if ($r['statut'] === 'forfait'): ?><span class="pastille pastille-forfait" title="Forfait">F</span><?php endif; ?>
          <?php $alertes = $r['alertes'] ? json_decode($r['alertes'], true) : []; if ($alertes !== []): ?><span class="pastille" title="<?= e(implode(', ', array_map(fn($c) => \Agca\Domain\Controles::LIBELLES[$c] ?? $c, $alertes))) ?>"><?= count($alertes) ?></span><?php endif; ?>
          <?php if (!empty($utilisateur) && $r['statut'] !== 'a_jouer' && ($utilisateur['est_admin'] || in_array((int) $utilisateur['equipe_id'], [(int) $r['recevant_id'], (int) $r['invite_id']], true))): ?><a href="/rencontre/<?= e($r['id']) ?>">feuille</a><?php endif; ?>
        </td>
      </tr>
    <?php $precedente = $cle; endforeach; ?>
    </tbody></table></div>
<?php endforeach; ?>
<p class="aide">Une pastille orange signale une feuille à vérifier par l'administrateur (index, jokers, feuille incomplète). F : forfait.</p>
