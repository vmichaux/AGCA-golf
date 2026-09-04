<h1>Actualités</h1>
<p><a class="bouton" href="/admin/contenu/actualites/nouvelle">Nouvelle actualité</a></p>
<div class="tableau-defilant"><table><thead><tr><th>Titre</th><th>Date</th><th>État</th><th></th></tr></thead><tbody>
<?php foreach ($actualites as $a): ?>
  <tr><td><a href="/admin/contenu/actualites/<?= e($a['id']) ?>"><?= e($a['titre']) ?></a></td><td><?= e(fmt_date($a['date_publication'])) ?></td><td><?= $a['publie'] ? 'publiée' : 'brouillon' ?></td>
  <td><form method="post" action="/admin/contenu/actualites/<?= e($a['id']) ?>/supprimer" class="inline" onsubmit="return confirm('Supprimer cette actualité ?')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-danger">Supprimer</button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div>
