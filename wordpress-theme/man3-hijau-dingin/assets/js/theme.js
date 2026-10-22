(function () {
  var toggle = document.querySelector('[data-menu-toggle]');
  var panel = document.querySelector('[data-nav-mobile]');
  var icon = document.querySelector('[data-menu-icon]');
  if (!toggle || !panel) return;

  toggle.addEventListener('click', function () {
    var open = panel.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (icon) icon.textContent = open ? '✕' : '☰';
  });
})();
