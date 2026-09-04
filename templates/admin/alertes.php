<div class="entete-page"><div class="conteneur">
  <span class="etiquette">Administration</span>
  <h1>Feuilles à vérifier</h1>
</div></div>
<section class="bloc"><div class="conteneur">
<?php if ($rencontres === []): ?><p>Aucune alerte en attente.</p><?php endif; ?>
<?php foreach ($rencontres as $r): ?>
  <div class="panneau">
    <p><strong><?= e($r['division_libelle']) ?></strong> · <?= e(ucfirst($r['journee_phase']) . ' J' . $r['journee_numero']) ?> · <?= e(fmt_date($r['date_reelle'])) ?> · <a href="/rencontre/<?= e($r['id']) ?>"><?= e($r['recevant_nom']) ?> – <?= e($r['invite_nom']) ?></a> (<?= e($r['total_pour']) ?> - <?= e($r['total_contre']) ?>)</p>
    <ul class="alertes"><?php foreach (json_decode($r['alertes'], true) as $c): ?><li><span class="pastille">!</span> <?= e(\Agca\Domain\Controles::LIBELLES[$c] ?? $c) ?></li><?php endforeach; ?></ul>
    <div class="actions">
      <form method="post" action="/admin/rencontre/<?= e($r['id']) ?>/alertes-vues"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="bouton bouton-secondaire">Marquer comme vérifiée</button></form>
      <a class="bouton" href="/rencontre/<?= e($r['id']) ?>/saisie">Corriger la feuille</a>
    </div>
  </div>
<?php endforeach; ?>
</div></section>
