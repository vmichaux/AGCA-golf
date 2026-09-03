<h1>Championnats interclubs AGCA<?= $saison ? ' — saison ' . e($saison['libelle']) : '' ?></h1>
<p>L'Association des Golfs de la Coupe de l'Amitié organise deux championnats interclubs par équipes en région PACA.</p>
<ul class="cartes">
  <?php foreach ($series as $s): ?>
    <li><a href="/serie/<?= e($s['code']) ?>/classements"><strong><?= e($s['libelle']) ?></strong><br>
      <?= $s['code'] === 'M' ? '10 joueurs par équipe, index 11,5 à 22, 5 doubles et 10 simples sur 9 trous en net.' : '5 joueurs classés par index, 1 double et 4 simples sur 18 trous en brut.' ?><br>
      <span class="aide">Classements · Calendrier et suivi · Feuille vierge · Règlement</span></a></li>
  <?php endforeach; ?>
</ul>
<p><a class="bouton" href="/connexion">Espace capitaine</a></p>
