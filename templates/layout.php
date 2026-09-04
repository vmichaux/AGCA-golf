<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titre ?? 'AGCA') ?></title>
<?php if (!empty($description)): ?>
<meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<link rel="icon" href="/img/logo-agca.jpg">
<link rel="stylesheet" href="/css/agca.css">
</head>
<body>
<a class="evitement" href="#contenu">Aller au contenu</a>
<?= $vue->inclure('partials/entete') ?>
<main id="contenu">
<?php if (!empty($flashs)): ?>
  <div class="conteneur zone-flash">
    <?php foreach ($flashs as $f): ?><div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>
<?= $contenu ?>
</main>
<?= $vue->inclure('partials/pied') ?>
<script src="/js/site.js" defer></script>
</body>
</html>
