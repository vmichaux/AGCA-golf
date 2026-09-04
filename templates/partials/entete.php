<header class="entete"><div class="conteneur">
  <a class="marque" href="/"><img src="/img/logo-agca.jpg" alt="" width="40" height="40"><div><strong>AGCA</strong><?php if (empty($utilisateur)): ?><span>Association des Golfs de la Coupe de l'Amitié</span><?php endif; ?></div></a>
  <button class="burger" type="button" aria-label="Menu" aria-expanded="false" aria-controls="menu-principal">Menu ☰</button>
  <nav class="nav" id="menu-principal" aria-label="Navigation principale">
    <div class="deroulant">
      <a href="/serie/M/classements" aria-haspopup="true" aria-expanded="false">Championnats</a>
      <ul>
        <li><a href="/serie/M/classements">Mixte 2e série</a></li>
        <li><a href="/serie/H1/classements">Homme 1re série</a></li>
      </ul>
    </div>
    <a href="/competitions">Compétitions</a>
    <a href="/golfs">Golfs membres</a>
    <a href="/photos">Photos</a>
    <div class="deroulant">
      <a href="/association/organigramme" aria-haspopup="true" aria-expanded="false">Association</a>
      <ul>
        <li><a href="/association/organigramme">Organigramme</a></li>
        <?php foreach ($menuPages ?? [] as $p): ?>
        <li><a href="/page/<?= e($p['slug']) ?>"><?= e($p['titre']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="/contact">Contact</a></li>
      </ul>
    </div>
    <?php if (!empty($utilisateur)): ?>
      <a class="cta" href="/capitaine"><?= e($utilisateur['identifiant']) ?></a>
      <?php if ($utilisateur['est_admin']): ?><a href="/admin">Admin</a><?php endif; ?>
      <form method="post" action="/deconnexion" class="deconnexion"><input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>"><button type="submit" class="lien">Déconnexion</button></form>
    <?php else: ?>
      <a class="cta" href="/connexion">Espace capitaine</a>
    <?php endif; ?>
  </nav>
</div></header>
