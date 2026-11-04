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

  /* Info popup — beranda (DOM ready: markup diprint setelah script footer WP) */
  function initMan3Popup() {
    var popup = document.querySelector('[data-man3-popup]');
    if (!popup) return;

    var cookieKey = popup.getAttribute('data-cookie-key') || 'man3_popup_hide';
    var hours = parseInt(popup.getAttribute('data-cookie-hours') || '24', 10);
    if (isNaN(hours) || hours < 1) hours = 24;
    var snooze = popup.querySelector('[data-man3-popup-snooze]');
    var lastFocus = null;

    function readCookie(name) {
      var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
      return m ? decodeURIComponent(m[1]) : '';
    }

    function writeCookie(name, value, hrs) {
      var maxAge = Math.floor(hrs * 3600);
      var secure = location.protocol === 'https:' ? '; Secure' : '';
      document.cookie = name + '=' + encodeURIComponent(value) + '; Path=/; Max-Age=' + maxAge + '; SameSite=Lax' + secure;
    }

    function openPopup() {
      if (readCookie(cookieKey) === '1') return;
      lastFocus = document.activeElement;
      popup.hidden = false;
      popup.removeAttribute('hidden');
      document.documentElement.classList.add('man3-popup-open');
      var closeBtn = popup.querySelector('.man3-popup__x');
      if (closeBtn) closeBtn.focus();
    }

    function closePopup() {
      if (snooze && snooze.checked) {
        writeCookie(cookieKey, '1', hours);
      }
      popup.hidden = true;
      popup.setAttribute('hidden', 'hidden');
      document.documentElement.classList.remove('man3-popup-open');
      if (lastFocus && typeof lastFocus.focus === 'function') {
        try { lastFocus.focus(); } catch (e) { /* ignore */ }
      }
    }

    popup.querySelectorAll('[data-man3-popup-close]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        closePopup();
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !popup.hidden) closePopup();
    });

    window.setTimeout(openPopup, 400);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMan3Popup);
  } else {
    initMan3Popup();
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
