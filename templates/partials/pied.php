<footer class="pied"><div class="conteneur">
  <div>
    <h4>AGCA</h4>
    <p>Association des Golfs de la Coupe de l'Amitié</p>
    <p>c/o Robert Michaux (Bobby)<br>11 rue de la Verrerie, 13100 Aix-en-Provence</p>
    <p>06 22 85 58 22 · <a href="mailto:romichaux@wanadoo.fr">romichaux@wanadoo.fr</a></p>
  </div>
  <div>
    <h4>Le site</h4>
    <p><a href="/serie/M/classements">Classements et calendrier</a></p>
    <p><a href="/competitions">Compétitions</a></p>
    <p><a href="/golfs">Golfs membres</a></p>
    <p><a href="/photos">Photos</a></p>
    <p><a href="<?= !empty($utilisateur) ? '/capitaine' : '/connexion' ?>">Espace capitaine</a></p>
  </div>
  <div>
    <h4>L'association</h4>
    <p><a href="/association/organigramme">Organigramme</a></p>
    <?php foreach ($menuPages ?? [] as $p): ?>
    <p><a href="/page/<?= e($p['slug']) ?>"><?= e($p['titre']) ?></a></p>
    <?php endforeach; ?>
    <p><a href="/contact">Contact</a></p>
    <p><a href="http://www.ffgolf.org/">Fédération française de golf</a></p>
  </div>
  <div class="bas">
    <span>© <?= date('Y') ?> AGCA — Interclubs de golf en Provence-Alpes-Côte d'Azur</span>
    <span>Photo : parcours de Cannes-Mougins, yourgolftravel.com, CC BY-SA 4.0</span>
  </div>
</div></footer>
