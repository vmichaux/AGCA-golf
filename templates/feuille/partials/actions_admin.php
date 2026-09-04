<div class="panneau">
  <h2>Actions administrateur</h2>
  <div class="actions-admin">
    <form method="post" action="/rencontre/<?= e($rencontre['id']) ?>/date">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <label for="date_reelle">Date réelle</label>
      <div class="actions"><input id="date_reelle" type="date" name="date_reelle" value="<?= e($rencontre['date_reelle']) ?>" class="champ-date"> <button class="bouton bouton-secondaire">Modifier la date</button></div>
    </form>
    <?php if ($rencontre['statut'] === 'forfait'): ?>
      <form method="post" action="/admin/rencontre/<?= e($rencontre['id']) ?>/annuler-forfait" onsubmit="return confirm('Annuler le forfait ?')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-danger">Annuler le forfait</button></form>
    <?php else: ?>
      <form method="post" action="/admin/rencontre/<?= e($rencontre['id']) ?>/forfait" onsubmit="return confirm('Déclarer ce forfait ? La feuille éventuelle sera effacée.')">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="actions">
          <select name="camp" class="champ-auto"><option value="recevant">Forfait de <?= e($rencontre['recevant_nom']) ?></option><option value="invite">Forfait de <?= e($rencontre['invite_nom']) ?></option></select>
          <button class="bouton bouton-danger">Déclarer le forfait</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
