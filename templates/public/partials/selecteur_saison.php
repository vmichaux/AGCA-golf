<nav class="sous-nav">
  <a href="/serie/<?= e($serie['code']) ?>/classements">Classements</a>
  <a href="/serie/<?= e($serie['code']) ?>/suivi">Calendrier et suivi</a>
  <a href="/serie/<?= e($serie['code']) ?>/feuille-vierge">Feuille vierge</a>
  <a href="/serie/<?= e($serie['code']) ?>/reglement">Règlement</a>
  <?php if (count($saisons) > 1): ?>
  <form method="get" class="inline">
    <select name="saison" onchange="this.form.submit()" aria-label="Saison">
      <?php foreach ($saisons as $s): ?>
        <option value="<?= e($s['id']) ?>" <?= $saison && $s['id'] == $saison['id'] ? 'selected' : '' ?>><?= e($s['libelle']) ?><?= $s['statut'] === 'gelee' ? ' (archivée)' : '' ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <?php endif; ?>
</nav>
