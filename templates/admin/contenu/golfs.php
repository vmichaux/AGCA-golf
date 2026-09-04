<h1>Golfs</h1>
<div class="tableau-defilant"><table><thead><tr><th>Nom</th><th>Ville</th><th>Site web</th><th>Membre</th><th class="num">Ordre</th><th></th></tr></thead><tbody>
<?php foreach ($golfs as $g): ?>
  <tr>
    <td><input name="nom" value="<?= e($g['nom']) ?>" form="golf-<?= e($g['id']) ?>"></td>
    <td><input name="ville" value="<?= e($g['ville'] ?? '') ?>" form="golf-<?= e($g['id']) ?>"></td>
    <td><input name="site_web" value="<?= e($g['site_web'] ?? '') ?>" form="golf-<?= e($g['id']) ?>"></td>
    <td><input type="checkbox" name="membre" value="1" <?= !empty($g['membre']) ? 'checked' : '' ?> form="golf-<?= e($g['id']) ?>" style="width:auto"></td>
    <td class="num"><input name="ordre" type="number" class="champ-court" value="<?= e($g['ordre']) ?>" form="golf-<?= e($g['id']) ?>"></td>
    <td><button class="bouton bouton-secondaire" form="golf-<?= e($g['id']) ?>">Enregistrer</button></td>
  </tr>
<?php endforeach; ?>
</tbody></table></div>
<?php foreach ($golfs as $g): ?><form id="golf-<?= e($g['id']) ?>" method="post" action="/admin/contenu/golfs/<?= e($g['id']) ?>"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"></form><?php endforeach; ?>

<h2>Ajouter un golf</h2>
<form method="post" action="/admin/contenu/golfs">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="nom">Nom</label><input id="nom" name="nom" required>
  <label for="ville">Ville</label><input id="ville" name="ville">
  <label for="site_web">Site web</label><input id="site_web" name="site_web">
  <label><input type="checkbox" name="membre" value="1" checked style="width:auto"> Membre</label>
  <label for="ordre">Ordre</label><input id="ordre" name="ordre" type="number" class="champ-court" value="0">
  <p><button class="bouton">Ajouter</button></p>
</form>
