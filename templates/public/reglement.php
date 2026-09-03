<h1>Règlement — <?= e($serie['libelle']) ?></h1>
<?= $vue->inclure('public/partials/selecteur_saison', ['serie' => $serie, 'saison' => null, 'saisons' => []]) ?>
<?php if ($serie['code'] === 'M'): ?>
<h2>Équipes</h2><p>10 joueuses ou joueurs par équipe, index de 11,5 à 22. Un joker homme et une joker dame d'index inférieur à 11,5 sont admis par rencontre.</p>
<h2>Formule</h2><p>5 doubles et 10 simples sur 9 trous, en net. 15 parties, 30 points de match en jeu : partie gagnée 2, nulle 1, perdue 0.</p>
<?php else: ?>
<h2>Équipes</h2><p>5 joueurs classés par ordre d'index, index libre.</p>
<h2>Formule</h2><p>1 double et 4 simples sur 18 trous, en brut. 5 parties, 15 points de match en jeu : partie gagnée 3, nulle 2, perdue 1.</p>
<?php endif; ?>
<h2>Points de rencontre</h2>
<ul><li>Rencontre gagnée : 3 points ; nulle : 2 points ; perdue : 1 point.</li><li>Bonus : victoire à l'extérieur +1 ; nul à l'extérieur +0,5.</li><li>Forfait : 0 point au forfaitaire, 3 points au bénéficiaire (+1 s'il était invité), score de parties 15-0. Déclaré par l'administrateur.</li></ul>
<h2>Classement</h2>
<ol><li>Total des points de rencontre, bonus inclus.</li><li>Confrontation directe : points de rencontre cumulés sur les matchs aller et retour, bonus inclus.</li><li>Différence des points de parties pour − contre sur la saison.</li><li>Ex æquo.</li></ol>
<h2>Feuille de match</h2><p>Saisie sur le site par le capitaine recevant, sans délai imposé, immédiatement comptée au classement. Le capitaine invité et l'administrateur en sont informés par e-mail. Seul l'administrateur peut corriger une feuille enregistrée. Report : la nouvelle date est convenue entre les deux capitaines et saisie par le recevant.</p>
