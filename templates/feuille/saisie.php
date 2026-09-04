<?= $vue->inclure('feuille/partials/entete', ['rencontre' => $rencontre, 'titre_page' => $correction ? 'Corriger la feuille' : 'Saisir la feuille de match']) ?>
<section class="bloc"><div class="conteneur">
<?php foreach ($erreurs as $err): ?><div class="flash flash-erreur"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" action="/rencontre/<?= e($rencontre['id']) ?>/saisie" id="feuille"
      data-pts-gagne="<?= $serie->ptsGagne ?>" data-pts-nul="<?= $serie->ptsNul ?>" data-pts-perdu="<?= $serie->ptsPerdu ?>"
      data-golf-rec="<?= e($rencontre['recevant_golf_id']) ?>" data-golf-inv="<?= e($rencontre['invite_golf_id']) ?>">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <div class="panneau">
    <label for="date_reelle">Date réelle du match</label>
    <input id="date_reelle" name="date_reelle" type="date" class="champ-date" value="<?= e($post['date_reelle'] ?? $rencontre['date_reelle']) ?>" required>
    <p class="aide">En cas de report, indiquez la date convenue avec le capitaine adverse.</p>
  </div>

  <?php foreach ($structure as $s): ?>
    <?= $vue->inclure('feuille/partials/partie', ['s' => $s, 'serie' => $serie, 'rencontre' => $rencontre, 'v' => $post['p'][$s['numero']] ?? []]) ?>
  <?php endforeach; ?>

  <div class="totaux-direct">
    <span><?= e($rencontre['recevant_nom']) ?> : <strong id="total-rec">0</strong></span>
    <span><?= e($rencontre['invite_nom']) ?> : <strong id="total-inv">0</strong></span>
    <span id="resume-rencontre" class="aide"></span>
  </div>
  <p class="actions">
    <button class="bouton" id="bouton-enregistrer"><?= $correction ? 'Enregistrer la correction' : 'Enregistrer la feuille' ?></button>
    <a class="bouton bouton-secondaire" href="<?= $correction ? '/rencontre/' . e($rencontre['id']) : '/capitaine' ?>">Annuler</a>
  </p>
  <p class="aide">Résultat vu de l'équipe recevante. Score facultatif : 3&2, 1UP, AS. Les index sont saisis avec une décimale (ex. 14,3). <?= $serie->mixte ? 'H/D obligatoire pour le contrôle des jokers.' : '' ?><br>
  Une fois enregistrée, la feuille compte immédiatement au classement et ne peut être corrigée que par l'administrateur.</p>
</form>
</div></section>
<script src="/js/feuille.js"></script>
