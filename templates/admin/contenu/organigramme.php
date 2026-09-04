<h1>Organigramme</h1>
<div class="tableau-defilant"><table><thead><tr><th>Groupe</th><th>Fonction</th><th>Prénom</th><th>Nom</th><th>Golf</th><th class="num">Ordre</th><th></th></tr></thead><tbody>
<?php foreach ($lignes as $l): ?>
  <tr>
    <td><select name="groupe" form="org-<?= e($l['id']) ?>" style="width:auto"><option value="bureau" <?= $l['groupe'] === 'bureau' ? 'selected' : '' ?>>Bureau</option><option value="ca" <?= $l['groupe'] === 'ca' ? 'selected' : '' ?>>Conseil d'administration</option></select></td>
    <td><input name="fonction" value="<?= e($l['fonction']) ?>" form="org-<?= e($l['id']) ?>"></td>
    <td><input name="prenom" value="<?= e($l['prenom'] ?? '') ?>" form="org-<?= e($l['id']) ?>"></td>
    <td><input name="nom" value="<?= e($l['nom']) ?>" form="org-<?= e($l['id']) ?>"></td>
    <td><input name="golf" value="<?= e($l['golf'] ?? '') ?>" form="org-<?= e($l['id']) ?>"></td>
    <td class="num"><input name="ordre" type="number" class="champ-court" value="<?= e($l['ordre']) ?>" form="org-<?= e($l['id']) ?>"></td>
    <td><button class="bouton bouton-secondaire" form="org-<?= e($l['id']) ?>">Enregistrer</button> <button class="bouton bouton-danger" form="org-sup-<?= e($l['id']) ?>" onclick="return confirm('Supprimer cette ligne ?')">Supprimer</button></td>
  </tr>
<?php endforeach; ?>
</tbody></table></div>
<?php foreach ($lignes as $l): ?>
  <form id="org-<?= e($l['id']) ?>" method="post" action="/admin/contenu/organigramme/<?= e($l['id']) ?>"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"></form>
  <form id="org-sup-<?= e($l['id']) ?>" method="post" action="/admin/contenu/organigramme/<?= e($l['id']) ?>/supprimer"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"></form>
<?php endforeach; ?>

<h2>Ajouter une ligne</h2>
<form method="post" action="/admin/contenu/organigramme">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="groupe">Groupe</label><select id="groupe" name="groupe" style="width:auto"><option value="bureau">Bureau</option><option value="ca">Conseil d'administration</option></select>
  <label for="fonction">Fonction</label><input id="fonction" name="fonction" required>
  <label for="prenom">Prénom</label><input id="prenom" name="prenom">
  <label for="nom">Nom</label><input id="nom" name="nom" required>
  <label for="golf">Golf</label><input id="golf" name="golf">
  <label for="ordre">Ordre</label><input id="ordre" name="ordre" type="number" class="champ-court" value="0">
  <p><button class="bouton">Ajouter</button></p>
</form>
