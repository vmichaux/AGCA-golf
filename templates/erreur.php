<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Erreur <?= e($statut) ?></span>
  <h1><?= $statut == 404 ? 'Page introuvable' : 'Une erreur est survenue' ?></h1>
</div></div>
<section class="bloc"><div class="conteneur">
  <p><?= nl2br(e($message)) ?></p>
  <p class="actions"><a class="bouton" href="/">Retour à l'accueil</a></p>
</div></section>
