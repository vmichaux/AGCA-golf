<?php $actif = $actif ?? ''; ?>
<nav class="sous-nav" aria-label="Pages du championnat">
  <a href="/serie/<?= e($serie['code']) ?>/classements"<?= $actif === 'classements' ? ' class="actif" aria-current="page"' : '' ?>>Classements</a>
  <a href="/serie/<?= e($serie['code']) ?>/suivi"<?= $actif === 'suivi' ? ' class="actif" aria-current="page"' : '' ?>>Calendrier et suivi</a>
  <a href="/serie/<?= e($serie['code']) ?>/feuille-vierge"<?= $actif === 'feuille-vierge' ? ' class="actif" aria-current="page"' : '' ?>>Feuille vierge</a>
  <a href="/serie/<?= e($serie['code']) ?>/reglement"<?= $actif === 'reglement' ? ' class="actif" aria-current="page"' : '' ?>>Règlement</a>
  <?php if (count($saisons) > 1): ?>
  <form method="get">
    <select name="saison" onchange="this.form.submit()" aria-label="Saison">
      <?php foreach ($saisons as $s): ?>
        <option value="<?= e($s['id']) ?>" <?= $saison && $s['id'] == $saison['id'] ? 'selected' : '' ?>><?= e($s['libelle']) ?><?= $s['statut'] === 'gelee' ? ' (archivée)' : '' ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <?php endif; ?>
</nav>
