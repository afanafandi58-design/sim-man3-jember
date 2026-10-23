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
  if (!hero) return;

  var slides = Array.prototype.slice.call(hero.querySelectorAll('[data-hero-slide]'));
  var dots = Array.prototype.slice.call(hero.querySelectorAll('[data-hero-dot]'));
  var prevBtn = hero.querySelector('[data-hero-prev]');
  var nextBtn = hero.querySelector('[data-hero-next]');
  if (slides.length < 2) return;

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

  function next() {
    goTo(index + 1);
  }
  function prev() {
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
    timer = setInterval(next, interval);
  }

  if (nextBtn) nextBtn.addEventListener('click', function () { next(); start(); });
  if (prevBtn) prevBtn.addEventListener('click', function () { prev(); start(); });
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
})();
