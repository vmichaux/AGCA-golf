<div class="entete-page"><div class="conteneur">
  <span class="etiquette">AGCA<?= $saison ? ' · Saison ' . e($saison['libelle']) : '' ?></span>
  <h1>Administration</h1>
</div></div>
<section class="bloc"><div class="conteneur">
  <ul class="cartes">
    <li><a href="/admin/alertes"><strong>Feuilles à vérifier</strong><?= $nbAlertes ?> alerte(s) non traitée(s)</a></li>
    <li><a href="/admin/utilisateurs"><strong>Identifiants</strong>Mots de passe, droits admin, rattachement aux équipes</a></li>
    <li><a href="/admin/joueurs"><strong>Joueurs</strong>Corrections et fusion de doublons par golf</a></li>
    <li><a href="/admin/saisons"><strong>Saisons</strong>Nouvelle saison, divisions, gel</a></li>
    <li><a href="/admin/contenu"><strong>Contenu du site</strong>Pages, actualités, compétitions, golfs, organigramme, albums, documents</a></li>
    <?php foreach ($series as $s): ?><li><a href="/serie/<?= e($s['code']) ?>/suivi"><strong><?= e($s['libelle']) ?></strong>Suivi et accès aux feuilles (correction, forfait, date)</a></li><?php endforeach; ?>
  </ul>
  <h2>Feuilles non saisies 48 h après la date du match</h2>
  <?php if ($enRetard === []): ?><p>Aucune.</p><?php else: ?>
  <ul><?php foreach ($enRetard as $r): ?><li><?= e(fmt_date($r['date_reelle'])) ?> — <?= e($r['division_libelle']) ?> — <a href="/rencontre/<?= e($r['id']) ?>"><?= e($r['recevant_nom']) ?> – <?= e($r['invite_nom']) ?></a></li><?php endforeach; ?></ul>
  <?php endif; ?>
  <h2>Dernières actions</h2>
  <ul class="journal"><?php foreach ($journal as $l): ?><li><?= e($l['quand']) ?> — <?= e($l['identifiant'] ?? 'système') ?> — <?= e($l['action']) ?><?= $l['cible_type'] === 'rencontre' ? ' — <a href="/rencontre/' . e($l['cible_id']) . '">rencontre ' . e($l['cible_id']) . '</a>' : '' ?></li><?php endforeach; ?></ul>
</div></section>
