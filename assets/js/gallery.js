/**
 * Moris public gallery — category filter + lightbox.
 * Vanilla JS, no dependencies.
 */
(function () {
  'use strict';

  // ---------- Category filter ----------
  var filterBtns = document.querySelectorAll('.filter-btn');
  var items = document.querySelectorAll('.gallery-item');

  filterBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var filter = btn.getAttribute('data-filter');

      filterBtns.forEach(function (b) {
        b.classList.remove('is-active');
      });
      btn.classList.add('is-active');

      items.forEach(function (item) {
        var show = filter === 'all' || item.classList.contains(filter);
        item.style.display = show ? '' : 'none';
      });
    });
  });

  // ---------- Lightbox ----------
  var links = Array.prototype.slice.call(document.querySelectorAll('.gallery-link'));
  var lightbox = document.getElementById('lightbox');
  if (!lightbox || links.length === 0) {
    return;
  }

  var lbImg = document.getElementById('lightbox-img');
  var lbCaption = document.getElementById('lightbox-caption');
  var btnClose = document.getElementById('lightbox-close');
  var btnPrev = document.getElementById('lightbox-prev');
  var btnNext = document.getElementById('lightbox-next');
  var current = 0;

  function visibleLinks() {
    return links.filter(function (a) {
      return a.closest('.gallery-item').style.display !== 'none';
    });
  }

  function show(list, index) {
    if (index < 0) index = list.length - 1;
    if (index >= list.length) index = 0;
    current = index;
    var a = list[index];
    lbImg.setAttribute('src', a.getAttribute('href'));
    var title = a.getAttribute('data-title') || '';
    lbImg.setAttribute('alt', title);
    lbCaption.textContent = title;
    lbCaption.style.display = title ? '' : 'none';
  }

  function open(a) {
    var list = visibleLinks();
    var idx = list.indexOf(a);
    show(list, idx < 0 ? 0 : idx);
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function close() {
    lightbox.hidden = true;
    document.body.style.overflow = '';
  }

  links.forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      open(a);
    });
  });

  btnClose.addEventListener('click', close);
  btnPrev.addEventListener('click', function () {
    show(visibleLinks(), current - 1);
  });
  btnNext.addEventListener('click', function () {
    show(visibleLinks(), current + 1);
  });

  lightbox.addEventListener('click', function (e) {
    if (e.target === lightbox) {
      close();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (lightbox.hidden) return;
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowLeft') show(visibleLinks(), current - 1);
    else if (e.key === 'ArrowRight') show(visibleLinks(), current + 1);
  });
})();
