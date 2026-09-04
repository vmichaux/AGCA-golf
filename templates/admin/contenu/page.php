<h1><?= e($titre) ?></h1>
<?php foreach ($erreurs as $err): ?><div class="flash flash-erreur"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" action="<?= $page['id'] ? '/admin/contenu/pages/' . e($page['id']) : '/admin/contenu/pages' ?>">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="titre">Titre</label><input id="titre" name="titre" value="<?= e($page['titre']) ?>" required>
  <?php if (empty($page['systeme'])): ?>
    <label for="slug">Adresse (slug)</label><input id="slug" name="slug" value="<?= e($page['slug']) ?>" pattern="[a-z0-9-]{2,80}" required><p class="aide">La page sera visible à /page/&lt;adresse&gt;.</p>
  <?php else: ?><p class="aide">Adresse fixe : <?= e($page['slug']) ?> (page requise par le site).</p><?php endif; ?>
  <label><input type="checkbox" name="dans_menu" value="1" <?= !empty($page['dans_menu']) ? 'checked' : '' ?> style="width:auto"> Afficher dans le menu « Association »</label>
  <label for="ordre">Ordre</label><input id="ordre" name="ordre" type="number" class="champ-court" value="<?= e($page['ordre'] ?? 0) ?>">
  <?= $vue->inclure('admin/contenu/partials/editeur', ['nom' => 'corps_md', 'valeur' => $page['corps_md'] ?? '', 'libelle' => 'Contenu']) ?>
  <?php if ($documents !== []): ?>
    <h2>Documents joints</h2>
    <?php foreach ($documents as $d): ?><label><input type="checkbox" name="documents[]" value="<?= e($d['id']) ?>" <?= in_array((int) $d['id'], $joints, true) ? 'checked' : '' ?> style="width:auto"> <?= e($d['titre']) ?> <span class="aide">(<?= e($d['categorie']) ?>)</span></label><?php endforeach; ?>
  <?php endif; ?>
  <p><button class="bouton">Enregistrer</button> <a class="bouton bouton-secondaire" href="/admin/contenu/pages">Retour</a></p>
</form>
<script src="/js/editeur.js"></script>
