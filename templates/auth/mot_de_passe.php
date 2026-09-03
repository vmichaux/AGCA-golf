<h1>Changer mon mot de passe</h1>
<?php if (!empty($erreur)): ?><div class="flash flash-erreur"><?= e($erreur) ?></div><?php endif; ?>
<form method="post" action="/mot-de-passe">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="actuel">Mot de passe actuel</label>
  <input id="actuel" type="password" name="actuel" required autocomplete="current-password">
  <label for="nouveau">Nouveau mot de passe (8 caractères minimum)</label>
  <input id="nouveau" type="password" name="nouveau" required minlength="8" autocomplete="new-password">
  <label for="confirmation">Confirmation</label>
  <input id="confirmation" type="password" name="confirmation" required minlength="8" autocomplete="new-password">
  <p><button class="bouton">Enregistrer</button> <a class="bouton bouton-secondaire" href="/capitaine">Annuler</a></p>
</form>
