<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titre ?? 'AGCA') ?></title>
<link rel="stylesheet" href="/css/agca.css">
</head>
<body>
<header class="entete">
  <a class="marque" href="/">AGCA <span>Interclubs</span></a>
  <nav class="nav">
    <a href="/serie/M/classements">Mixte 2e série</a>
    <a href="/serie/H1/classements">Homme 1re série</a>
    <?php if (!empty($utilisateur)): ?>
      <a href="/capitaine"><?= e($utilisateur['identifiant']) ?></a>
      <?php if ($utilisateur['est_admin']): ?><a href="/admin">Admin</a><?php endif; ?>
      <form method="post" action="/deconnexion" class="inline"><input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>"><button class="lien">Déconnexion</button></form>
    <?php else: ?>
      <a href="/connexion">Espace capitaine</a>
    <?php endif; ?>
  </nav>
</header>
<main class="contenu">
  <?php foreach ($flashs ?? [] as $f): ?>
    <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>
  <?= $contenu ?>
</main>
<footer class="pied">AGCA — Association des Golfs de la Coupe de l'Amitié · <a href="/">Accueil</a></footer>
</body>
</html>
