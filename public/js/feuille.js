(function () {
  'use strict';
  var form = document.getElementById('feuille');
  if (!form) return;
  var pts = { G: [+form.dataset.ptsGagne, +form.dataset.ptsPerdu], N: [+form.dataset.ptsNul, +form.dataset.ptsNul], P: [+form.dataset.ptsPerdu, +form.dataset.ptsGagne] };
  var golfs = { rec: form.dataset.golfRec, inv: form.dataset.golfInv };
  var cache = {};

  // Auto-complétion par golf : remplit la datalist du camp avec les joueurs déjà connus.
  function charger(camp, q) {
    var cle = camp + ':' + q.toLowerCase();
    if (cache[cle]) { remplir(camp, cache[cle]); return; }
    fetch('/api/joueurs?golf=' + encodeURIComponent(golfs[camp]) + '&q=' + encodeURIComponent(q), { credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : []; })
      .then(function (liste) { cache[cle] = liste; remplir(camp, liste); })
      .catch(function () {});
  }
  function remplir(camp, liste) {
    var dl = document.getElementById('liste-' + camp);
    if (!dl) return;
    dl.innerHTML = '';
    liste.forEach(function (j) {
      var o = document.createElement('option');
      o.value = j.nom;
      o.label = (j.dernier_index !== null ? 'index ' + j.dernier_index : '') + (j.sexe ? ' · ' + j.sexe : '');
      o.dataset.index = j.dernier_index === null ? '' : j.dernier_index;
      o.dataset.sexe = j.sexe || '';
      dl.appendChild(o);
    });
  }
  form.querySelectorAll('input.nom').forEach(function (inp) {
    inp.addEventListener('focus', function () { charger(inp.dataset.camp, ''); });
    inp.addEventListener('input', function () { charger(inp.dataset.camp, inp.value); });
    // Quand un nom connu est choisi, pré-remplir index et H/D s'ils sont vides.
    inp.addEventListener('change', function () {
      var dl = document.getElementById('liste-' + inp.dataset.camp);
      var opt = dl && Array.prototype.find.call(dl.options, function (o) { return o.value.toUpperCase() === inp.value.trim().toUpperCase(); });
      if (!opt) return;
      var bloc = inp.parentElement;
      var index = bloc.querySelector('.index'), sexe = bloc.querySelector('.sexe');
      if (index && !index.value && opt.dataset.index) index.value = opt.dataset.index;
      if (sexe && !sexe.value && opt.dataset.sexe) sexe.value = opt.dataset.sexe;
    });
  });

  // Totaux en direct.
  function recalculer() {
    var rec = 0, inv = 0, manquants = 0;
    form.querySelectorAll('fieldset.partie').forEach(function (f) {
      var coche = f.querySelector('input[type=radio]:checked');
      var aff = f.querySelector('.pts-partie');
      if (!coche) { manquants++; aff.textContent = ''; return; }
      var p = pts[coche.value];
      rec += p[0]; inv += p[1];
      aff.textContent = p[0] + ' – ' + p[1];
    });
    document.getElementById('total-rec').textContent = rec;
    document.getElementById('total-inv').textContent = inv;
    var resume = rec > inv ? 'Rencontre gagnée par le recevant (3 pts, invité 1 pt)' : rec < inv ? 'Rencontre gagnée par l\'invité (3 pts + 1 bonus, recevant 1 pt)' : 'Rencontre nulle (2 pts chacun, +0,5 bonus invité)';
    document.getElementById('resume-rencontre').textContent = resume + (manquants ? ' — ' + manquants + ' partie(s) sans résultat' : '');
  }
  form.addEventListener('change', recalculer);
  recalculer();

  // Confirmation avant envoi.
  form.addEventListener('submit', function (ev) {
    var manquants = Array.prototype.filter.call(form.querySelectorAll('fieldset.partie'), function (f) { return !f.querySelector('input[type=radio]:checked'); }).length;
    var vides = Array.prototype.filter.call(form.querySelectorAll('input.nom'), function (i) { return !i.value.trim(); }).length;
    var msg = 'Enregistrer la feuille : ' + document.getElementById('total-rec').textContent + ' – ' + document.getElementById('total-inv').textContent + ' ?';
    if (manquants || vides) msg += '\n\nAttention : ' + (manquants ? manquants + ' partie(s) sans résultat. ' : '') + (vides ? vides + ' joueur(s) non renseigné(s). ' : '') + 'La feuille sera enregistrée avec une alerte pour l\'administrateur.';
    msg += '\n\nAprès enregistrement, seule l\'administration pourra corriger.';
    if (!window.confirm(msg)) ev.preventDefault();
  });
})();
