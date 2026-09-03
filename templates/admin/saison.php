<h1>Saison <?= e($saison['libelle']) ?> (<?= e($saison['statut']) ?>)</h1>
<?php foreach ($erreurs as $err): ?><div class="flash flash-erreur"><?= e($err) ?></div><?php endforeach; ?>
<?php foreach ($series as $s): ?>
  <h2><?= e($s['libelle']) ?></h2>
  <p class="aide">Journées : <?= e(implode(' · ', array_map(fn($j) => ucfirst($j['phase'][0]) . $j['numero'] . ' ' . fmt_date($j['date_calendrier']), $s['journees']))) ?: 'aucune' ?></p>
  <?php foreach ($s['divisions'] as $d): ?>
    <p><strong><?= e($d['libelle']) ?></strong> : <?= e(implode(', ', array_map(fn($e) => $e['position'] . '. ' . $e['nom'], $d['equipes']))) ?> — <a href="/serie/<?= e($s['code']) ?>/suivi?saison=<?= e($saison['id']) ?>">suivi</a></p>
  <?php endforeach; ?>
  <?php if ($saison['statut'] === 'active' && $s['equipes_libres'] !== []): ?>
  <form method="post" action="/admin/saisons/<?= e($saison['id']) ?>/divisions" class="entete-feuille">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="serie_id" value="<?= e($s['id']) ?>">
    <label>Nouvelle division — libellé</label><input name="libelle" placeholder="DIV2/POULE B" required>
    <?php for ($pos = 1; $pos <= 5; $pos++): ?>
      <label>Position <?= $pos ?><?= $pos === 5 ? ' (poule à 5 seulement)' : '' ?></label>
      <select name="equipe[<?= $pos ?>]"><option value="">—</option><?php foreach ($s['equipes_libres'] as $e): ?><option value="<?= e($e['id']) ?>"><?= e($e['nom']) ?></option><?php endforeach; ?></select>
    <?php endfor; ?>
    <p><button class="bouton">Créer la division et générer les rencontres</button></p>
  </form>
  <?php endif; ?>
<?php endforeach; ?>
<p><a class="bouton bouton-secondaire" href="/admin/saisons">Retour aux saisons</a></p>
