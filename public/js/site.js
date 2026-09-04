/* Menu principal : ouverture/fermeture sur mobile, sous-menus au clic. */
(function () {
  'use strict';
  var entete = document.querySelector('.entete');
  if (!entete) { return; }
  var bouton = entete.querySelector('.burger');
  var nav = entete.querySelector('.nav');
  if (!bouton || !nav) { return; }
  var deroulants = Array.prototype.slice.call(entete.querySelectorAll('.deroulant'));

  function tactile() { return window.matchMedia('(max-width: 899px)').matches; }

  function fermerSousMenus() {
    deroulants.forEach(function (d) {
      d.classList.remove('ouvert');
      var a = d.querySelector('a');
      if (a) { a.setAttribute('aria-expanded', 'false'); }
    });
  }

  function ouvrirMenu(ouvert) {
    nav.classList.toggle('ouvert', ouvert);
    bouton.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
    if (!ouvert) { fermerSousMenus(); }
  }

  bouton.addEventListener('click', function () {
    ouvrirMenu(!nav.classList.contains('ouvert'));
  });

  deroulants.forEach(function (d) {
    var lien = d.querySelector('a');
    if (!lien) { return; }
    lien.addEventListener('click', function (ev) {
      if (!tactile()) { return; }
      ev.preventDefault();
      var ouvert = d.classList.contains('ouvert');
      fermerSousMenus();
      if (!ouvert) { d.classList.add('ouvert'); lien.setAttribute('aria-expanded', 'true'); }
    });
  });

  document.addEventListener('click', function (ev) {
    if (!entete.contains(ev.target)) { ouvrirMenu(false); }
  });

  document.addEventListener('keydown', function (ev) {
    if (ev.key !== 'Escape' && ev.key !== 'Esc') { return; }
    if (nav.classList.contains('ouvert')) { ouvrirMenu(false); bouton.focus(); }
    else { fermerSousMenus(); }
  });
})();
