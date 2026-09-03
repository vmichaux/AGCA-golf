<h1>Espace capitaine</h1>
<?php if (!empty($erreur)): ?><div class="flash flash-erreur"><?= e($erreur) ?></div><?php endif; ?>
<form method="post" action="/connexion" class="formulaire">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="identifiant">Équipe (identifiant)</label>
  <input id="identifiant" name="identifiant" value="<?= e($identifiant) ?>" required autocomplete="username" autocapitalize="characters">
  <label for="serie">Série</label>
  <select id="serie" name="serie">
    <?php foreach ($series as $s): ?>
      <option value="<?= e($s['code']) ?>" <?= $s['code'] === $serie ? 'selected' : '' ?>><?= e($s['libelle']) ?></option>
    <?php endforeach; ?>
  </select>
  <label for="mot_de_passe">Mot de passe</label>
  <input id="mot_de_passe" type="password" name="mot_de_passe" required autocomplete="current-password">
  <p><button class="bouton">Se connecter</button></p>
  <p class="aide">Votre identifiant est le nom de votre équipe, inchangé. En cas d'oubli du mot de passe, contactez l'administrateur.</p>
</form>
