<?php
/**
 * Sous-navigation des pages « Association » : mêmes entrées que le menu déroulant de l'en-tête.
 * $actif : chemin de la page courante (facultatif).
 */
$liens = [['/association/organigramme', 'Organigramme']];
foreach ($menuPages ?? [] as $p) { $liens[] = ['/page/' . $p['slug'], (string) $p['titre']]; }
$liens[] = ['/contact', 'Contact'];
?>
<nav class="sous-nav" aria-label="L'association">
<?php foreach ($liens as [$href, $libelle]): ?><a<?= $href === ($actif ?? '') ? ' class="actif"' : '' ?> href="<?= e($href) ?>"><?= e($libelle) ?></a><?php endforeach; ?>
</nav>
