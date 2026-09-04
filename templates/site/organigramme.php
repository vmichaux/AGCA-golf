<?php
/** Bureau directeur puis conseil d'administration. */
$blocs = [['bureau', 'Bureau directeur'], ['ca', 'Conseil d\'administration']];
?>
<div class="entete-page"><div class="conteneur">
  <span class="etiquette">L'association</span>
  <h1>Organigramme</h1>
  <?= $vue->inclure('site/partials/nav_association', ['actif' => '/association/organigramme']) ?>
</div></div>
<section class="bloc"><div class="conteneur">
  <?php if ($groupes['bureau'] === [] && $groupes['ca'] === []): ?>
  <p class="aide">L'organigramme sera publié prochainement.</p>
  <?php endif; ?>
  <?php foreach ($blocs as [$cle, $libelle]): ?>
  <?php if ($groupes[$cle] !== []): ?>
  <div class="division">
    <h2><?= e($libelle) ?></h2>
    <div class="carte tableau-defilant"><table>
      <thead><tr><th>Fonction</th><th>Prénom</th><th>Nom</th><th>Golf</th></tr></thead>
      <tbody>
      <?php foreach ($groupes[$cle] as $l): ?>
        <tr><td><?= e($l['fonction']) ?></td><td><?= e($l['prenom']) ?></td><td><?= e($l['nom']) ?></td><td><?= e($l['golf']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
  <?php endif; ?>
  <?php endforeach; ?>
</div></section>
