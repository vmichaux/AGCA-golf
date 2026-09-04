<h1>Pages</h1>
<p><a class="bouton" href="/admin/contenu/pages/nouvelle">Nouvelle page</a></p>
<div class="tableau-defilant"><table><thead><tr><th>Titre</th><th>Adresse</th><th>Menu</th><th>Modifiée le</th><th></th></tr></thead><tbody>
<?php foreach ($pages as $p): ?>
  <tr><td><a href="/admin/contenu/pages/<?= e($p['id']) ?>"><?= e($p['titre']) ?></a><?= $p['systeme'] ? ' <span class="aide">(page du site)</span>' : '' ?></td><td>/page/<?= e($p['slug']) ?></td><td><?= $p['dans_menu'] ? 'oui' : '' ?></td><td><?= e($p['modifie_le']) ?></td>
  <td><?php if (!$p['systeme']): ?><form method="post" action="/admin/contenu/pages/<?= e($p['id']) ?>/supprimer" class="inline" onsubmit="return confirm('Supprimer cette page ?')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-danger">Supprimer</button></form><?php endif; ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
