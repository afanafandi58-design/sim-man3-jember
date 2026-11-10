(function () {
  var toggle = document.querySelector('[data-menu-toggle]');
  var panel = document.querySelector('[data-nav-mobile]');
  var icon = document.querySelector('[data-menu-icon]');
  if (toggle && panel) {
    toggle.addEventListener('click', function () {
      var open = panel.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (icon) icon.textContent = open ? '✕' : '☰';
    });
  }

  /* Desktop: buka dropdown parent saat hover */
  var desktop = document.querySelector('.nav-desktop');
  if (desktop && window.matchMedia) {
    var mq = window.matchMedia('(min-width: 980px)');
    function bindHover() {
      var items = desktop.querySelectorAll('details.nav-item');
      items.forEach(function (item) {
        if (item.dataset.hoverBound) return;
        item.dataset.hoverBound = '1';
        var closeTimer;
        item.addEventListener('mouseenter', function () {
          if (!mq.matches) return;
          clearTimeout(closeTimer);
          items.forEach(function (other) {
            if (other !== item) other.open = false;
          });
          item.open = true;
        });
        item.addEventListener('mouseleave', function () {
          if (!mq.matches) return;
          closeTimer = setTimeout(function () {
            item.open = false;
          }, 160);
        });
      });
    }
    bindHover();
  }

  /* Hero slider — autoplay + dots/arrows */
  var hero = document.querySelector('[data-hero-slider]');
  if (hero) {
    var slides = Array.prototype.slice.call(hero.querySelectorAll('[data-hero-slide]'));
    var dots = Array.prototype.slice.call(hero.querySelectorAll('[data-hero-dot]'));
    var prevBtn = hero.querySelector('[data-hero-prev]');
    var nextBtn = hero.querySelector('[data-hero-next]');
    if (slides.length >= 2) {
      var index = 0;
      var interval = parseInt(hero.getAttribute('data-hero-interval') || '5500', 10);
      if (isNaN(interval) || interval < 2500) interval = 5500;
      var timer = null;
      var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      function goTo(next) {
        var n = ((next % slides.length) + slides.length) % slides.length;
        if (n === index) return;
        slides[index].classList.remove('is-active');
        slides[n].classList.add('is-active');
        if (dots.length) {
          if (dots[index]) {
            dots[index].classList.remove('is-active');
            dots[index].setAttribute('aria-selected', 'false');
          }
          if (dots[n]) {
            dots[n].classList.add('is-active');
            dots[n].setAttribute('aria-selected', 'true');
          }
        }
        index = n;
      }

      function nextSlide() {
        goTo(index + 1);
      }
      function prevSlide() {
        goTo(index - 1);
      }

      function stop() {
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
      }
      function start() {
        if (reduceMotion) return;
        stop();
        timer = setInterval(nextSlide, interval);
      }

      if (nextBtn) nextBtn.addEventListener('click', function () { nextSlide(); start(); });
      if (prevBtn) prevBtn.addEventListener('click', function () { prevSlide(); start(); });
      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          var i = parseInt(dot.getAttribute('data-hero-dot') || '0', 10);
          goTo(i);
          start();
        });
      });

      hero.addEventListener('mouseenter', stop);
      hero.addEventListener('mouseleave', start);
      hero.addEventListener('focusin', stop);
      hero.addEventListener('focusout', function (e) {
        if (!hero.contains(e.relatedTarget)) start();
      });

      document.addEventListener('visibilitychange', function () {
        if (document.hidden) stop();
        else start();
      });

      start();
    }
  }

  /* Slide informasi — strip di bawah menu (bukan modal) */
  function initMan3InfoStrip() {
    var root = document.querySelector('[data-info-slider]');
    if (!root) return;

    var slides = Array.prototype.slice.call(root.querySelectorAll('[data-info-slide]'));
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-info-dot]'));
    var prevBtn = root.querySelector('[data-info-prev]');
    var nextBtn = root.querySelector('[data-info-next]');
    var status = root.querySelector('[data-info-status]');
    if (slides.length < 1) return;

    var index = 0;
    var interval = parseInt(root.getAttribute('data-info-interval') || '5000', 10);
    if (isNaN(interval) || interval < 3000) interval = 5000;
    var timer = null;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var touchStartX = 0;
    var touchDeltaX = 0;

    function goTo(next) {
      var n = ((next % slides.length) + slides.length) % slides.length;
      if (n === index && slides[n].classList.contains('is-active')) return;
      slides[index].classList.remove('is-active');
      slides[index].setAttribute('hidden', 'hidden');
      slides[n].classList.add('is-active');
      slides[n].removeAttribute('hidden');
      if (dots.length) {
        if (dots[index]) {
          dots[index].classList.remove('is-active');
          dots[index].setAttribute('aria-selected', 'false');
        }
        if (dots[n]) {
          dots[n].classList.add('is-active');
          dots[n].setAttribute('aria-selected', 'true');
        }
      }
      index = n;
      if (status) status.textContent = (index + 1) + ' / ' + slides.length;
    }

    function nextSlide() { goTo(index + 1); }
    function prevSlide() { goTo(index - 1); }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }
    function start() {
      if (reduceMotion || slides.length < 2) return;
      stop();
      timer = setInterval(nextSlide, interval);
    }

    if (slides.length >= 2) {
      if (nextBtn) nextBtn.addEventListener('click', function () { nextSlide(); start(); });
      if (prevBtn) prevBtn.addEventListener('click', function () { prevSlide(); start(); });
      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          var i = parseInt(dot.getAttribute('data-info-dot') || '0', 10);
          goTo(i);
          start();
        });
      });

      root.addEventListener('mouseenter', stop);
      root.addEventListener('mouseleave', start);
      root.addEventListener('focusin', stop);
      root.addEventListener('focusout', function (e) {
        if (!root.contains(e.relatedTarget)) start();
      });

      root.addEventListener('touchstart', function (e) {
        if (!e.changedTouches || !e.changedTouches[0]) return;
        touchStartX = e.changedTouches[0].clientX;
        touchDeltaX = 0;
        stop();
      }, { passive: true });
      root.addEventListener('touchmove', function (e) {
        if (!e.changedTouches || !e.changedTouches[0]) return;
        touchDeltaX = e.changedTouches[0].clientX - touchStartX;
      }, { passive: true });
      root.addEventListener('touchend', function () {
        if (Math.abs(touchDeltaX) > 40) {
          if (touchDeltaX < 0) nextSlide();
          else prevSlide();
        }
        start();
      });

      document.addEventListener('visibilitychange', function () {
        if (document.hidden) stop();
        else start();
      });

      start();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMan3InfoStrip);
  } else {
    initMan3InfoStrip();
  }

  /* Galeri Foto — filter jenis + lightbox */
  function initMan3FotoGallery() {
    var grid = document.querySelector('[data-foto-grid]');
    if (!grid) return;

    var cards = Array.prototype.slice.call(document.querySelectorAll('[data-foto-card]'));
    var filters = Array.prototype.slice.call(document.querySelectorAll('[data-foto-filter]'));
    var empty = document.querySelector('[data-foto-empty]');
    var countEl = document.querySelector('[data-foto-count]');
    var totalLabel = countEl ? countEl.textContent : '';

    function setFilter(slug) {
      var visible = 0;
      cards.forEach(function (card) {
        var match = slug === 'all' || card.getAttribute('data-jenis') === slug;
        card.classList.toggle('is-hidden', !match);
        if (match) visible += 1;
      });
      filters.forEach(function (btn) {
        var on = btn.getAttribute('data-foto-filter') === slug;
        btn.classList.toggle('is-active', on);
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
      if (empty) empty.hidden = visible > 0;
      if (countEl) {
        countEl.textContent = visible + ' foto kegiatan';
        if (slug === 'all' && totalLabel) countEl.textContent = totalLabel;
      }
    }

    filters.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setFilter(btn.getAttribute('data-foto-filter') || 'all');
      });
    });

    var dialog = document.querySelector('[data-foto-lightbox]');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    var img = dialog.querySelector('[data-foto-lightbox-img]');
    var titleEl = dialog.querySelector('[data-foto-lightbox-title]');
    var jenisEl = dialog.querySelector('[data-foto-lightbox-jenis]');
    var dateEl = dialog.querySelector('[data-foto-lightbox-date]');
    var descEl = dialog.querySelector('[data-foto-lightbox-desc]');

    function openLightbox(btn) {
      if (!img) return;
      img.src = btn.getAttribute('data-full') || '';
      img.alt = btn.getAttribute('data-title') || '';
      if (titleEl) titleEl.textContent = btn.getAttribute('data-title') || '';
      if (jenisEl) {
        jenisEl.textContent = btn.getAttribute('data-jenis-label') || '';
        jenisEl.hidden = !jenisEl.textContent;
      }
      if (dateEl) {
        dateEl.textContent = btn.getAttribute('data-date') || '';
        dateEl.hidden = !dateEl.textContent;
      }
      if (descEl) {
        descEl.textContent = btn.getAttribute('data-desc') || '';
        descEl.hidden = !descEl.textContent;
      }
      dialog.showModal();
    }

    document.querySelectorAll('[data-foto-open]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        openLightbox(btn);
      });
    });

    var closeBtn = dialog.querySelector('[data-foto-close]');
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        dialog.close();
      });
    }
    dialog.addEventListener('click', function (e) {
      if (e.target === dialog) dialog.close();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMan3FotoGallery);
  } else {
    initMan3FotoGallery();
  }
})();
