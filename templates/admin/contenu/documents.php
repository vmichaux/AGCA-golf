<h1>Documents</h1>
<form method="post" action="/admin/contenu/documents" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="titre">Titre</label><input id="titre" name="titre" required>
  <label for="categorie">Catégorie</label><select id="categorie" name="categorie"><?php foreach ($categories as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?></select>
  <label for="fichier">Fichier</label><input id="fichier" name="fichier" type="file" required>
  <p class="aide">Types autorisés : pdf, doc, docx, xls, xlsx, jpg, jpeg, png (10 Mo maximum).</p>
  <p><button class="bouton">Téléverser</button></p>
</form>

<div class="tableau-defilant"><table><thead><tr><th>Titre</th><th>Catégorie</th><th>Fichier</th><th class="num">Taille</th><th>Téléversé le</th><th></th></tr></thead><tbody>
<?php foreach ($documents as $d): ?>
  <tr>
    <td><?= e($d['titre']) ?></td><td><?= e($d['categorie']) ?></td>
    <td><a href="/documents/<?= e($d['fichier']) ?>"><?= e($d['fichier']) ?></a></td>
    <td class="num"><?= e((string) round($d['taille'] / 1024)) ?> Ko</td>
    <td><?= e($d['televerse_le']) ?></td>
    <td><form method="post" action="/admin/contenu/documents/<?= e($d['id']) ?>/supprimer" class="inline" onsubmit="return confirm('Supprimer ce document ?')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-danger">Supprimer</button></form></td>
  </tr>
<?php endforeach; ?>
</tbody></table></div>
