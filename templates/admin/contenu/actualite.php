<h1><?= e($titre) ?></h1>
<?php foreach ($erreurs as $err): ?><div class="flash flash-erreur"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" action="<?= $actualite['id'] ? '/admin/contenu/actualites/' . e($actualite['id']) : '/admin/contenu/actualites' ?>">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="titre">Titre</label><input id="titre" name="titre" value="<?= e($actualite['titre']) ?>" required>
  <label for="date_publication">Date de publication</label><input id="date_publication" name="date_publication" type="date" value="<?= e($actualite['date_publication']) ?>" required>
  <label for="resume">Résumé</label><textarea id="resume" name="resume" rows="3"><?= e($actualite['resume'] ?? '') ?></textarea>
  <label><input type="checkbox" name="publie" value="1" <?= !empty($actualite['publie']) ? 'checked' : '' ?> style="width:auto"> Publiée</label>
  <?= $vue->inclure('admin/contenu/partials/editeur', ['nom' => 'corps_md', 'valeur' => $actualite['corps_md'] ?? '', 'libelle' => 'Contenu']) ?>
  <p><button class="bouton">Enregistrer</button> <a class="bouton bouton-secondaire" href="/admin/contenu/actualites">Retour</a></p>
</form>
<script src="/js/editeur.js"></script>
