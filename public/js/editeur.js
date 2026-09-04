/**
 * Aperçu en direct des textarea.markdown : reproduit la grammaire de Agca\Domain\Markdown.
 * Échappement HTML d'abord, puis découpage en blocs, puis mise en forme en ligne.
 */
(function () {
  'use strict';

  function echapper(texte) {
    return texte
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formater(t) {
    t = t.replace(/\*\*([\s\S]+?)\*\*/g, '<strong>$1</strong>');
    t = t.replace(/(?<![*\w])\*(?!\s)([\s\S]+?)(?<!\s)\*(?![*\w])/g, '<em>$1</em>');
    return t;
  }

  function enLigne(t) {
    var liens = [];
    t = t.replace(/\[([^\]]+)\]\(([^\s]+)\)/g, function (m, texte, url) {
      if (!/^(https?:\/\/|\/(?!\/)|mailto:|#)/i.test(url)) { return texte; }
      var html = '<a href="' + echapper(url) + '" rel="noopener">' + formater(texte) + '</a>';
      liens.push(html);
      return '\u0000' + (liens.length - 1) + '\u0000';
    });
    t = formater(t);
    liens.forEach(function (html, i) { t = t.split('\u0000' + i + '\u0000').join(html); });
    return t;
  }

  function tableau(rangees) {
    var cellules = function (r) { return r.replace(/^\|/, '').replace(/\|$/, '').split('|').map(function (c) { return c.trim(); }); };
    var tete = cellules(rangees[0]);
    var corps = rangees.slice(1);
    if (corps.length > 0 && /^\|[\s:\-|]+\|?$/.test(corps[0])) { corps = corps.slice(1); }
    var html = '<table>\n<thead><tr>' + tete.map(function (c) { return '<th>' + enLigne(c) + '</th>'; }).join('') + '</tr></thead>\n<tbody>\n';
    corps.forEach(function (r) {
      html += '<tr>' + cellules(r).map(function (c) { return '<td>' + enLigne(c) + '</td>'; }).join('') + '</tr>\n';
    });
    return html + '</tbody>\n</table>';
  }

  function rendre(md) {
    var texte = md.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    texte = echapper(texte);
    var lignes = texte.split('\n');
    var blocs = [];
    var i = 0;
    var n = lignes.length;
    while (i < n) {
      var l = lignes[i];
      if (l.trim() === '') { i++; continue; }

      var mTitre = l.match(/^(#{1,2})\s+(.+)$/);
      if (mTitre) {
        var niveau = mTitre[1].length + 1;
        blocs.push('<h' + niveau + '>' + enLigne(mTitre[2].trim()) + '</h' + niveau + '>');
        i++; continue;
      }

      if (/^\s*[-*]\s+/.test(l)) {
        var itemsUl = [];
        var mUl;
        while (i < n && (mUl = lignes[i].match(/^\s*[-*]\s+(.*)$/))) { itemsUl.push('<li>' + enLigne(mUl[1].trim()) + '</li>'); i++; }
        blocs.push('<ul>\n' + itemsUl.join('\n') + '\n</ul>');
        continue;
      }

      if (/^\s*\d+[.)]\s+/.test(l)) {
        var itemsOl = [];
        var mOl;
        while (i < n && (mOl = lignes[i].match(/^\s*\d+[.)]\s+(.*)$/))) { itemsOl.push('<li>' + enLigne(mOl[1].trim()) + '</li>'); i++; }
        blocs.push('<ol>\n' + itemsOl.join('\n') + '\n</ol>');
        continue;
      }

      if (l.trim().indexOf('|') === 0) {
        var rangees = [];
        while (i < n && lignes[i].trim().indexOf('|') === 0) { rangees.push(lignes[i].trim()); i++; }
        blocs.push(tableau(rangees));
        continue;
      }

      var para = [];
      while (i < n && lignes[i].trim() !== '' && !/^(#{1,2}\s|\s*[-*]\s|\s*\d+[.)]\s|\s*\|)/.test(lignes[i])) {
        para.push(lignes[i].trim());
        i++;
      }
      blocs.push('<p>' + para.map(enLigne).join('<br>\n') + '</p>');
    }
    return blocs.join('\n');
  }

  function initialiser() {
    var zones = document.querySelectorAll('textarea.markdown');
    zones.forEach(function (zone) {
      var cibleId = zone.getAttribute('data-apercu');
      var cible = cibleId ? document.getElementById(cibleId) : null;
      if (!cible) { return; }
      var delai = null;
      var majApercu = function () {
        cible.innerHTML = rendre(zone.value);
      };
      zone.addEventListener('input', function () {
        if (delai) { clearTimeout(delai); }
        delai = setTimeout(majApercu, 200);
      });
      majApercu();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiser);
  } else {
    initialiser();
  }
})();
