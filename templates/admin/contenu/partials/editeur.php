<label for="<?= e($nom) ?>"><?= e($libelle) ?></label>
<div class="editeur">
  <textarea id="<?= e($nom) ?>" name="<?= e($nom) ?>" rows="16" class="markdown" data-apercu="apercu-<?= e($nom) ?>"><?= e($valeur) ?></textarea>
  <div class="apercu" id="apercu-<?= e($nom) ?>" aria-live="polite"></div>
</div>
<p class="aide">Mise en forme : <code># Titre</code>, <code>## Sous-titre</code>, <code>**gras**</code>, <code>*italique*</code>, <code>[texte](https://…)</code>, listes <code>- </code> ou <code>1. </code>, tableaux <code>| a | b |</code>. Une ligne vide sépare les paragraphes.</p>
