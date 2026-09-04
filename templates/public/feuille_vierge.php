<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Championnat · <?= e($serie['libelle']) ?></span>
  <h1 class="impression-titre">AGCA — <?= e($serie['libelle']) ?> — Feuille de match</h1>
  <p class="actions no-print"><button type="button" class="bouton" onclick="window.print()">Imprimer</button> <a class="bouton bouton-secondaire" href="/serie/<?= e($serie['code']) ?>/classements">Retour</a></p>
</div></div>
<section class="bloc"><div class="conteneur">
  <div class="carte tableau-defilant"><table class="vierge">
    <tr><th>Journée n°</th><td></td><th>Golf recevant</th><td></td><th>Golf invité</th><td></td><th>Date</th><td></td></tr>
  </table></div>
  <div class="carte tableau-defilant"><table class="vierge">
    <thead><tr><th>#</th><th>Joueur recevant</th><th>Index</th><?php if ($serie['serie']->mixte): ?><th>H/D</th><?php endif; ?><th>Score</th><th>Résultat (G/N/P)</th><th>Joueur invité</th><th>Index</th><?php if ($serie['serie']->mixte): ?><th>H/D</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($serie['serie']->structure as $i => $type): ?>
      <tr class="<?= $type === 'D' ? 'double' : '' ?>"><td><?= $i + 1 ?></td><td><?= $type === 'D' ? 'DOUBLE' : '' ?></td><td></td><?php if ($serie['serie']->mixte): ?><td></td><?php endif; ?><td></td><td></td><td><?= $type === 'D' ? 'DOUBLE' : '' ?></td><td></td><?php if ($serie['serie']->mixte): ?><td></td><?php endif; ?></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <p>Points de match : gagné <?= $serie['serie']->ptsGagne ?>, nul <?= $serie['serie']->ptsNul ?>, perdu <?= $serie['serie']->ptsPerdu ?>. Points de rencontre : gagnée 3, nulle 2, perdue 1, forfait 0 (score 15-0). Bonus : victoire à l'extérieur +1, nul à l'extérieur +0,5.</p>
  <p class="aide">Le capitaine recevant saisit cette feuille sur le site dans son espace capitaine.</p>
</div></section>
