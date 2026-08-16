document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.querySelector('.wpnfinite-nav-toggle');
  const nav = document.querySelector('.wpnfinite-navigation');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      const expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', String(!expanded));
      nav.classList.toggle('is-open');
    });
  }
  const submenuParents = document.querySelectorAll('.wpnfinite-navigation .menu-item-has-children > a');
  submenuParents.forEach(function (link) {
    link.addEventListener('click', function (event) {
      if (window.innerWidth > 991) return;
      const parent = link.parentElement;
      if (parent && !parent.classList.contains('is-open')) {
        event.preventDefault();
        parent.classList.add('is-open');
      }
    });
  });
});
