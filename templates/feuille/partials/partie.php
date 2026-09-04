<?php $n = $s['numero']; $nb = $s['type'] === 'double' ? 2 : 1; ?>
<fieldset class="partie <?= $s['type'] ?>">
  <legend>Partie <?= $n ?> — <?= $s['type'] === 'double' ? 'Double' : 'Simple' ?></legend>
  <div class="camps">
    <?php foreach (['rec' => $rencontre['recevant_nom'], 'inv' => $rencontre['invite_nom']] as $camp => $nomEquipe): ?>
      <div class="camp camp-<?= $camp ?>">
        <div class="camp-titre"><?= e($nomEquipe) ?></div>
        <?php for ($k = 1; $k <= $nb; $k++): $cle = "{$camp}{$k}"; ?>
          <div class="joueur">
            <input name="p[<?= $n ?>][<?= $cle ?>_nom]" value="<?= e($v["{$cle}_nom"] ?? '') ?>" placeholder="Nom" list="liste-<?= $camp ?>" class="nom" data-camp="<?= $camp ?>" autocapitalize="characters" autocomplete="off">
            <input name="p[<?= $n ?>][<?= $cle ?>_index]" value="<?= e($v["{$cle}_index"] ?? '') ?>" placeholder="Index" class="champ-court index" inputmode="decimal">
            <?php if ($serie->mixte): ?>
              <select name="p[<?= $n ?>][<?= $cle ?>_sexe]" class="champ-court sexe" aria-label="H ou D">
                <option value="">H/D</option>
                <option value="H" <?= ($v["{$cle}_sexe"] ?? '') === 'H' ? 'selected' : '' ?>>H</option>
                <option value="D" <?= ($v["{$cle}_sexe"] ?? '') === 'D' ? 'selected' : '' ?>>D</option>
              </select>
            <?php endif; ?>
          </div>
        <?php endfor; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="resultat">
    <span>Résultat (recevant) :</span>
    <?php foreach (['G' => 'Gagné', 'N' => 'Nul', 'P' => 'Perdu'] as $code => $lib): ?>
      <label class="radio"><input type="radio" name="p[<?= $n ?>][resultat]" value="<?= $code ?>" <?= ($v['resultat'] ?? '') === $code ? 'checked' : '' ?>> <?= $lib ?></label>
    <?php endforeach; ?>
    <input name="p[<?= $n ?>][score]" value="<?= e($v['score'] ?? '') ?>" placeholder="Score (3&2, 1UP, AS)" class="score champ-score">
    <span class="pts-partie aide"></span>
  </div>
</fieldset>
<?php if ($n === 1): ?><datalist id="liste-rec"></datalist><datalist id="liste-inv"></datalist><?php endif; ?>
