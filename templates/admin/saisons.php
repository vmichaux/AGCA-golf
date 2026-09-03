<h1>Saisons</h1>
<table><thead><tr><th>Saison</th><th>Statut</th><th></th></tr></thead><tbody>
<?php foreach ($saisons as $s): ?>
  <tr><td><a href="/admin/saisons/<?= e($s['id']) ?>"><?= e($s['libelle']) ?></a></td><td><?= e($s['statut']) ?></td>
    <td><?php if ($s['statut'] === 'active'): ?><form method="post" action="/admin/saisons/<?= e($s['id']) ?>/geler" class="inline" onsubmit="return confirm('Geler la saison ? Plus aucune saisie ne sera possible.')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-secondaire">Geler</button></form>
      <?php else: ?><form method="post" action="/admin/saisons/<?= e($s['id']) ?>/activer" class="inline"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-secondaire">Activer</button></form><?php endif; ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<h2>Nouvelle saison</h2>
<?php foreach ($erreurs as $err): ?><div class="flash flash-erreur"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" action="/admin/saisons">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="libelle">Libellé</label><input id="libelle" name="libelle" value="<?= e($post['libelle'] ?? '') ?>" placeholder="2027-28" required>
  <label for="date_debut">Début</label><input id="date_debut" type="date" name="date_debut" value="<?= e($post['date_debut'] ?? '') ?>" required style="max-width:12rem">
  <label for="date_fin">Fin</label><input id="date_fin" type="date" name="date_fin" value="<?= e($post['date_fin'] ?? '') ?>" required style="max-width:12rem">
  <?php foreach ($series as $s): ?>
    <h3><?= e($s['libelle']) ?> — dates des journées (laisser vide les journées inutilisées ; une poule à 4 utilise les 3 premières de chaque phase, une poule à 5 les 5)</h3>
    <div class="camps">
      <?php foreach (['aller' => 'Aller', 'retour' => 'Retour'] as $phase => $lib): ?>
        <div><div class="camp-titre"><?= $lib ?></div>
        <?php for ($k = 0; $k < 5; $k++): ?><input type="date" name="<?= $phase ?>_<?= e($s['code']) ?>[]" value="<?= e($post[$phase . '_' . $s['code']][$k] ?? '') ?>" style="max-width:12rem;margin-bottom:.3rem"><br><?php endfor; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  <p><button class="bouton">Créer la saison</button> <span class="aide">La saison précédente est gelée automatiquement.</span></p>
</form>
