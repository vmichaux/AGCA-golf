<h1>Administration<?= $saison ? ' — saison ' . e($saison['libelle']) : '' ?></h1>
<ul class="cartes">
  <li><a href="/admin/alertes"><strong>Feuilles à vérifier</strong><br><?= $nbAlertes ?> alerte(s) non traitée(s)</a></li>
  <li><a href="/admin/utilisateurs"><strong>Identifiants</strong><br>Mots de passe, droits admin, rattachement aux équipes</a></li>
  <li><a href="/admin/joueurs"><strong>Joueurs</strong><br>Corrections et fusion de doublons par golf</a></li>
  <li><a href="/admin/saisons"><strong>Saisons</strong><br>Nouvelle saison, divisions, gel</a></li>
  <?php foreach ($series as $s): ?><li><a href="/serie/<?= e($s['code']) ?>/suivi"><strong><?= e($s['libelle']) ?></strong><br>Suivi et accès aux feuilles (correction, forfait, date)</a></li><?php endforeach; ?>
</ul>
<h2>Feuilles non saisies 48 h après la date du match</h2>
<?php if ($enRetard === []): ?><p>Aucune.</p><?php else: ?>
<ul><?php foreach ($enRetard as $r): ?><li><?= e(fmt_date($r['date_reelle'])) ?> — <?= e($r['division_libelle']) ?> — <a href="/rencontre/<?= e($r['id']) ?>"><?= e($r['recevant_nom']) ?> – <?= e($r['invite_nom']) ?></a></li><?php endforeach; ?></ul>
<?php endif; ?>
<h2>Dernières actions</h2>
<ul class="journal"><?php foreach ($journal as $l): ?><li><?= e($l['quand']) ?> — <?= e($l['identifiant'] ?? 'système') ?> — <?= e($l['action']) ?><?= $l['cible_type'] === 'rencontre' ? ' — <a href="/rencontre/' . e($l['cible_id']) . '">rencontre ' . e($l['cible_id']) . '</a>' : '' ?></li><?php endforeach; ?></ul>
