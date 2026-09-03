<h1><?= $equipe ? e($equipe['nom']) . ' — ' . e($equipe['golf_nom']) : 'Mon équipe' ?></h1>
<?php if (!$equipe || !$saison): ?>
  <p>Aucune équipe rattachée à votre identifiant ou aucune saison active. Contactez l'administrateur.</p>
<?php else: ?>
  <p><?= $division ? 'Division : <strong>' . e($division['libelle']) . '</strong> · ' : '' ?>Saison <?= e($saison['libelle']) ?> · <a href="/mot-de-passe">Changer mon mot de passe</a></p>
  <div class="tableau-defilant"><table>
    <thead><tr><th>Journée</th><th>Date</th><th>Rencontre</th><th class="num">Résultat</th><th>État</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rencontres as $r): ?>
      <tr>
        <td><?= e(ucfirst($r['journee_phase']) . ' J' . $r['journee_numero']) ?></td>
        <td><?= e(fmt_date($r['date_reelle'])) ?><?= $r['reportee'] ? ' <span class="report">(reporté)</span>' : '' ?></td>
        <td><?= $r['domicile'] ? '<strong>' . e($r['recevant_nom']) . '</strong> reçoit ' . e($r['invite_nom']) : e($r['recevant_nom']) . ' reçoit <strong>' . e($r['invite_nom']) . '</strong>' ?></td>
        <td class="num"><?= $r['statut'] === 'a_jouer' ? '–' : e($r['total_pour']) . ' - ' . e($r['total_contre']) ?></td>
        <td><?= e($r['etat']) ?></td>
        <td>
          <?php if ($r['statut'] === 'a_jouer' && $r['domicile']): ?><a class="bouton" href="/rencontre/<?= e($r['id']) ?>/saisie">Saisir la feuille</a>
          <?php elseif ($r['statut'] !== 'a_jouer'): ?><a href="/rencontre/<?= e($r['id']) ?>">Voir la feuille</a><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <p class="aide">Le capitaine recevant saisit la feuille après le match. L'adversaire et l'administrateur sont prévenus par e-mail. Pour corriger une feuille enregistrée, contactez l'administrateur.</p>
<?php endif; ?>
