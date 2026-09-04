<h1><?= e($titre) ?></h1>
<form method="post" action="/admin/contenu/competitions/<?= e($competition['id']) ?>">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="nom">Nom</label><input id="nom" name="nom" value="<?= e($competition['nom']) ?>" required>
  <label for="accroche">Accroche</label><input id="accroche" name="accroche" value="<?= e($competition['accroche'] ?? '') ?>">
  <label for="formule">Formule</label><input id="formule" name="formule" value="<?= e($competition['formule'] ?? '') ?>">
  <label for="ordre">Ordre</label><input id="ordre" name="ordre" type="number" class="champ-court" value="<?= e($competition['ordre']) ?>">
  <label><input type="checkbox" name="actif" value="1" <?= !empty($competition['actif']) ? 'checked' : '' ?> style="width:auto"> Active</label>
  <?= $vue->inclure('admin/contenu/partials/editeur', ['nom' => 'corps_md', 'valeur' => $competition['corps_md'] ?? '', 'libelle' => 'Présentation']) ?>
  <p><button class="bouton">Enregistrer</button> <a class="bouton bouton-secondaire" href="/admin/contenu/competitions">Retour</a></p>
</form>
<script src="/js/editeur.js"></script>

<h2>Palmarès</h2>
<div class="tableau-defilant"><table><thead><tr><th>Saison</th><th>Lieu</th><th>Vainqueur</th><th>Détail</th><th>Document</th><th></th></tr></thead><tbody>
<?php foreach ($palmares as $p): ?>
  <tr>
    <td><input name="saison" value="<?= e($p['saison']) ?>" form="pal-<?= e($p['id']) ?>" class="champ-court"></td>
    <td><input name="lieu" value="<?= e($p['lieu'] ?? '') ?>" form="pal-<?= e($p['id']) ?>"></td>
    <td><input name="vainqueur" value="<?= e($p['vainqueur'] ?? '') ?>" form="pal-<?= e($p['id']) ?>"></td>
    <td><textarea name="detail_md" rows="3" form="pal-<?= e($p['id']) ?>"><?= e($p['detail_md'] ?? '') ?></textarea></td>
    <td><select name="document_id" form="pal-<?= e($p['id']) ?>"><option value="">— aucun —</option><?php foreach ($documents as $d): ?><option value="<?= e($d['id']) ?>" <?= (int) ($p['document_id'] ?? 0) === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['titre']) ?></option><?php endforeach; ?></select></td>
    <td><button class="bouton bouton-secondaire" form="pal-<?= e($p['id']) ?>">Enregistrer</button> <button class="bouton bouton-danger" form="pal-sup-<?= e($p['id']) ?>" onclick="return confirm('Supprimer cette édition ?')">Supprimer</button></td>
  </tr>
<?php endforeach; ?>
</tbody></table></div>
<?php foreach ($palmares as $p): ?>
  <form id="pal-<?= e($p['id']) ?>" method="post" action="/admin/contenu/palmares/<?= e($p['id']) ?>"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"></form>
  <form id="pal-sup-<?= e($p['id']) ?>" method="post" action="/admin/contenu/palmares/<?= e($p['id']) ?>/supprimer"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"></form>
<?php endforeach; ?>

<h3>Ajouter une édition</h3>
<form method="post" action="/admin/contenu/competitions/<?= e($competition['id']) ?>/palmares">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="saison">Saison</label><input id="saison" name="saison" class="champ-court" required>
  <label for="lieu">Lieu</label><input id="lieu" name="lieu">
  <label for="vainqueur">Vainqueur</label><input id="vainqueur" name="vainqueur">
  <label for="detail_md">Détail</label><textarea id="detail_md" name="detail_md" rows="3"></textarea>
  <label for="document_id">Document</label><select id="document_id" name="document_id"><option value="">— aucun —</option><?php foreach ($documents as $d): ?><option value="<?= e($d['id']) ?>"><?= e($d['titre']) ?></option><?php endforeach; ?></select>
  <p><button class="bouton">Ajouter</button></p>
</form>
