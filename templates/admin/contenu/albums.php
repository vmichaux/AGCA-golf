<h1>Albums photo</h1>
<div class="tableau-defilant"><table><thead><tr><th>Titre</th><th>Année</th><th>Adresse</th><th class="num">Ordre</th><th></th></tr></thead><tbody>
<?php foreach ($albums as $a): ?>
  <tr>
    <td><input name="titre" value="<?= e($a['titre']) ?>" form="album-<?= e($a['id']) ?>"></td>
    <td><input name="annee" type="number" class="champ-court" value="<?= e($a['annee'] ?? '') ?>" form="album-<?= e($a['id']) ?>"></td>
    <td><input name="url" value="<?= e($a['url']) ?>" form="album-<?= e($a['id']) ?>"></td>
    <td class="num"><input name="ordre" type="number" class="champ-court" value="<?= e($a['ordre']) ?>" form="album-<?= e($a['id']) ?>"></td>
    <td><button class="bouton bouton-secondaire" form="album-<?= e($a['id']) ?>">Enregistrer</button> <button class="bouton bouton-danger" form="album-sup-<?= e($a['id']) ?>" onclick="return confirm('Supprimer cet album ?')">Supprimer</button></td>
  </tr>
<?php endforeach; ?>
</tbody></table></div>
<?php foreach ($albums as $a): ?>
  <form id="album-<?= e($a['id']) ?>" method="post" action="/admin/contenu/albums/<?= e($a['id']) ?>"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"></form>
  <form id="album-sup-<?= e($a['id']) ?>" method="post" action="/admin/contenu/albums/<?= e($a['id']) ?>/supprimer"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"></form>
<?php endforeach; ?>

<h2>Ajouter un album</h2>
<form method="post" action="/admin/contenu/albums">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="titre">Titre</label><input id="titre" name="titre" required>
  <label for="annee">Année</label><input id="annee" name="annee" type="number" class="champ-court">
  <label for="url">Adresse (URL)</label><input id="url" name="url" required>
  <label for="ordre">Ordre</label><input id="ordre" name="ordre" type="number" class="champ-court" value="0">
  <p><button class="bouton">Ajouter</button></p>
</form>
