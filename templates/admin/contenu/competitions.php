<h1>Compétitions</h1>
<div class="tableau-defilant"><table><thead><tr><th>Nom</th><th>Accroche</th><th class="num">Ordre</th><th>Active</th></tr></thead><tbody>
<?php foreach ($competitions as $c): ?>
  <tr><td><a href="/admin/contenu/competitions/<?= e($c['id']) ?>"><?= e($c['nom']) ?></a></td><td><?= e($c['accroche'] ?? '') ?></td><td class="num"><?= e($c['ordre']) ?></td><td><?= $c['actif'] ? 'oui' : 'non' ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
