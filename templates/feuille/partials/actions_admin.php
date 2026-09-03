<div class="entete-feuille">
  <h2>Actions administrateur</h2>
  <form method="post" action="/rencontre/<?= e($rencontre['id']) ?>/date" class="inline">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label for="date_reelle">Date réelle</label><input id="date_reelle" type="date" name="date_reelle" value="<?= e($rencontre['date_reelle']) ?>" style="max-width:12rem"> <button class="bouton bouton-secondaire">Modifier la date</button>
  </form>
  <?php if ($rencontre['statut'] === 'forfait'): ?>
    <form method="post" action="/admin/rencontre/<?= e($rencontre['id']) ?>/annuler-forfait" class="inline" onsubmit="return confirm('Annuler le forfait ?')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-danger">Annuler le forfait</button></form>
  <?php else: ?>
    <form method="post" action="/admin/rencontre/<?= e($rencontre['id']) ?>/forfait" class="inline" onsubmit="return confirm('Déclarer ce forfait ? La feuille éventuelle sera effacée.')">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <select name="camp" style="width:auto"><option value="recevant">Forfait de <?= e($rencontre['recevant_nom']) ?></option><option value="invite">Forfait de <?= e($rencontre['invite_nom']) ?></option></select>
      <button class="bouton bouton-danger">Déclarer le forfait</button>
    </form>
  <?php endif; ?>
</div>
