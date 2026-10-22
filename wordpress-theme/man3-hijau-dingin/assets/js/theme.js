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
  if (!desktop || !window.matchMedia) return;

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
})();
