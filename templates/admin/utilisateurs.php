<h1>Identifiants</h1>
<div class="tableau-defilant"><table>
  <thead><tr><th>Identifiant</th><th>Série</th><th>Équipe</th><th>Admin</th><th>Dernière connexion</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($utilisateurs as $u): ?>
    <tr>
      <td><?= e($u['identifiant']) ?></td><td><?= e($u['serie_code'] ?? '—') ?></td>
      <td>
        <form method="post" action="/admin/utilisateurs/<?= e($u['id']) ?>/rattacher" class="inline"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <select name="equipe_id" onchange="this.form.submit()" style="width:auto"><option value="">— aucune —</option>
          <?php foreach ($equipes as $sid => $liste): foreach ($liste as $e): ?><option value="<?= e($e['id']) ?>" <?= (int) $u['equipe_id'] === (int) $e['id'] ? 'selected' : '' ?>><?= e($e['nom']) ?> (<?= e(array_values(array_filter($series, fn($s) => $s['id'] == $sid))[0]['code']) ?>)</option><?php endforeach; endforeach; ?>
          </select></form>
      </td>
      <td><form method="post" action="/admin/utilisateurs/<?= e($u['id']) ?>/admin" class="inline"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="valeur" value="<?= $u['est_admin'] ? 0 : 1 ?>"><button class="lien" style="color:var(--vert)"><?= $u['est_admin'] ? 'Oui (retirer)' : 'Non (activer)' ?></button></form></td>
      <td><?= e($u['derniere_connexion'] ?? 'jamais') ?><?= $u['hash_sha1'] ? ' <span class="aide">(ancien mot de passe)</span>' : '' ?></td>
      <td><form method="post" action="/admin/utilisateurs/<?= e($u['id']) ?>/reinitialiser" class="inline" onsubmit="return confirm('Générer un nouveau mot de passe pour <?= e($u['identifiant']) ?> ?')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-secondaire">Réinitialiser le mot de passe</button></form></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<h2>Créer un identifiant</h2>
<form method="post" action="/admin/utilisateurs">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label for="identifiant">Identifiant (nom d'équipe)</label><input id="identifiant" name="identifiant" required>
  <label for="serie_id">Série</label><select id="serie_id" name="serie_id"><option value="">— admin sans série —</option><?php foreach ($series as $s): ?><option value="<?= e($s['id']) ?>"><?= e($s['libelle']) ?></option><?php endforeach; ?></select>
  <label for="equipe_id">Équipe</label><select id="equipe_id" name="equipe_id"><option value="">— aucune —</option><?php foreach ($equipes as $liste): foreach ($liste as $e): ?><option value="<?= e($e['id']) ?>"><?= e($e['nom']) ?></option><?php endforeach; endforeach; ?></select>
  <p><button class="bouton">Créer</button> <span class="aide">Le mot de passe temporaire s'affiche une seule fois.</span></p>
</form>
