<h1>Joueurs</h1>
<form method="get" class="inline"><label for="golf">Golf</label><select id="golf" name="golf" onchange="this.form.submit()" style="width:auto"><option value="">— choisir —</option><?php foreach ($golfs as $g): ?><option value="<?= e($g['id']) ?>" <?= $golfId === (int) $g['id'] ? 'selected' : '' ?>><?= e($g['nom']) ?></option><?php endforeach; ?></select></form>
<?php if ($golfId): ?>
<div class="tableau-defilant"><table>
  <thead><tr><th>Nom</th><th>Prénom</th><th>H/D</th><th class="num">Dernier index</th><th></th></tr></thead>
  <tbody><?php foreach ($joueurs as $j): ?>
    <tr><form method="post" action="/admin/joueurs/<?= e($j['id']) ?>"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <td><input name="nom" value="<?= e($j['nom']) ?>"></td><td><input name="prenom" value="<?= e($j['prenom']) ?>"></td>
      <td><select name="sexe" style="width:auto"><option value="">?</option><option value="H" <?= $j['sexe'] === 'H' ? 'selected' : '' ?>>H</option><option value="D" <?= $j['sexe'] === 'D' ? 'selected' : '' ?>>D</option></select></td>
      <td class="num"><?= e($j['dernier_index'] ?? '') ?></td><td><button class="bouton bouton-secondaire">Enregistrer</button></td>
    </form></tr>
  <?php endforeach; ?></tbody></table></div>
<h2>Fusionner deux doublons</h2>
<form method="post" action="/admin/joueurs/fusion" onsubmit="return confirm('Fusionner ? Les parties du doublon seront réaffectées, l\'opération est définitive.')">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label>Doublon à supprimer</label><select name="source_id"><?php foreach ($joueurs as $j): ?><option value="<?= e($j['id']) ?>"><?= e($j['nom']) ?> <?= e($j['prenom']) ?></option><?php endforeach; ?></select>
  <label>Joueur conservé</label><select name="cible_id"><?php foreach ($joueurs as $j): ?><option value="<?= e($j['id']) ?>"><?= e($j['nom']) ?> <?= e($j['prenom']) ?></option><?php endforeach; ?></select>
  <p><button class="bouton bouton-danger">Fusionner</button></p>
</form>
<?php endif; ?>
