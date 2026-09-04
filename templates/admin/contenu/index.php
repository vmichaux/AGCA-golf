<h1>Contenu du site</h1>
<ul class="cartes">
  <li><a href="/admin/contenu/pages"><strong>Pages</strong><br><?= e($nbPages) ?> page(s) : accueil, statuts, AG, voyage, mentions légales…</a></li>
  <li><a href="/admin/contenu/actualites"><strong>Actualités</strong><br><?= e($nbActualites) ?> actualité(s)</a></li>
  <li><a href="/admin/contenu/competitions"><strong>Compétitions</strong><br><?= e($nbCompetitions) ?> fiches et leur palmarès</a></li>
  <li><a href="/admin/contenu/golfs"><strong>Golfs membres</strong><br><?= e($nbGolfs) ?> golf(s) affichés</a></li>
  <li><a href="/admin/contenu/organigramme"><strong>Organigramme</strong><br>Bureau directeur et conseil d'administration</a></li>
  <li><a href="/admin/contenu/albums"><strong>Albums photo</strong><br>Liens vers les galeries</a></li>
  <li><a href="/admin/contenu/documents"><strong>Documents</strong><br><?= e($nbDocuments) ?> fichier(s) PDF / Word / Excel</a></li>
</ul>
<p><a class="bouton bouton-secondaire" href="/admin">Retour à l'administration</a></p>
