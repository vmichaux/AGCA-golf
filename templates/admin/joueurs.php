<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Administration</span>
  <h1>Joueurs</h1>
  <nav class="sous-nav" aria-label="Golf">
    <form method="get"><label class="visuellement-cache" for="golf">Golf</label><select id="golf" name="golf" onchange="this.form.submit()" class="champ-auto"><option value="">— choisir un golf —</option><?php foreach ($golfs as $g): ?><option value="<?= e($g['id']) ?>" <?= $golfId === (int) $g['id'] ? 'selected' : '' ?>><?= e($g['nom']) ?></option><?php endforeach; ?></select></form>
  </nav>
</div></div>
<section class="bloc"><div class="conteneur">
<?php if ($golfId): ?>
<div class="carte tableau-defilant"><table class="tableau-champs">
  <thead><tr><th>Nom</th><th>Prénom</th><th>H/D</th><th class="num">Dernier index</th><th></th></tr></thead>
  <tbody><?php foreach ($joueurs as $j): ?>
    <tr>
      <td><input name="nom" value="<?= e($j['nom']) ?>" form="joueur-<?= e($j['id']) ?>"></td><td><input name="prenom" value="<?= e($j['prenom']) ?>" form="joueur-<?= e($j['id']) ?>"></td>
      <td><select name="sexe" form="joueur-<?= e($j['id']) ?>" class="champ-auto"><option value="">?</option><option value="H" <?= $j['sexe'] === 'H' ? 'selected' : '' ?>>H</option><option value="D" <?= $j['sexe'] === 'D' ? 'selected' : '' ?>>D</option></select></td>
      <td class="num"><?= e($j['dernier_index'] ?? '') ?></td><td><button class="bouton bouton-secondaire" form="joueur-<?= e($j['id']) ?>">Enregistrer</button></td>
    </tr>
  <?php endforeach; ?></tbody></table></div>
<?php foreach ($joueurs as $j): ?><form id="joueur-<?= e($j['id']) ?>" method="post" action="/admin/joueurs/<?= e($j['id']) ?>"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"></form><?php endforeach; ?>
<h2>Fusionner deux doublons</h2>
<form method="post" action="/admin/joueurs/fusion" class="panneau formulaire" onsubmit="return confirm('Fusionner ? Les parties du doublon seront réaffectées, l\'opération est définitive.')">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="source_id">Doublon à supprimer</label><select id="source_id" name="source_id"><?php foreach ($joueurs as $j): ?><option value="<?= e($j['id']) ?>"><?= e($j['nom']) ?> <?= e($j['prenom']) ?></option><?php endforeach; ?></select>
  <label for="cible_id">Joueur conservé</label><select id="cible_id" name="cible_id"><?php foreach ($joueurs as $j): ?><option value="<?= e($j['id']) ?>"><?= e($j['nom']) ?> <?= e($j['prenom']) ?></option><?php endforeach; ?></select>
  <p class="actions"><button class="bouton bouton-danger">Fusionner</button></p>
</form>
<?php else: ?>
<p>Choisissez un golf pour afficher ses joueurs.</p>
<?php endif; ?>
</div></section>
