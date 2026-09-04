<?php
/** Formulaire de contact : jeton CSRF, champ piège `site_web` masqué, valeurs conservées en cas d'erreur. */
?>
<div class="entete-page"><div class="conteneur">
  <span class="etiquette">L'association</span>
  <h1>Contact</h1>
  <?= $vue->inclure('site/partials/nav_association', ['actif' => '/contact']) ?>
</div></div>
<section class="bloc"><div class="conteneur">
  <?php foreach ($erreurs as $erreur): ?>
  <div class="flash flash-erreur"><?= e($erreur) ?></div>
  <?php endforeach; ?>
  <form method="post" action="/contact" class="panneau formulaire">
    <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
    <label for="contact-nom">Nom</label>
    <input id="contact-nom" name="nom" type="text" required autocomplete="name" value="<?= e($valeurs['nom']) ?>">
    <label for="contact-email">Adresse e-mail</label>
    <input id="contact-email" name="email" type="email" required autocomplete="email" value="<?= e($valeurs['email']) ?>">
    <label for="contact-objet">Objet</label>
    <input id="contact-objet" name="objet" type="text" required value="<?= e($valeurs['objet']) ?>">
    <label for="contact-message">Message</label>
    <textarea id="contact-message" name="message" rows="8" required><?= e($valeurs['message']) ?></textarea>
    <div class="piege" aria-hidden="true">
      <label for="contact-site-web">Site web</label>
      <input id="contact-site-web" name="site_web" type="text" tabindex="-1" autocomplete="off" value="">
    </div>
    <p class="actions"><button class="bouton" type="submit">Envoyer le message</button></p>
  </form>
</div></section>
