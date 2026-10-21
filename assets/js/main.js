'use strict';
// Menu tetap dapat digunakan bila JavaScript Bootstrap gagal dimuat.
if (!window.bootstrap) {
    document.documentElement.classList.add('public-menu-fallback');
    document.querySelectorAll('.navbar-collapse, .dropdown-menu').forEach((menu) => {
        menu.classList.add('fallback-menu');
    });
    document.querySelectorAll('.navbar-toggler, .dropdown-toggle').forEach((button) => {
        button.hidden = true;
    });
} else {
    document.documentElement.classList.add('public-menu-ready');
    const menu = document.querySelector('#public-menu');
    const toggle = document.querySelector('.navbar-toggler');
    if (menu && toggle) {
        const mobile = window.matchMedia('(max-width: 1199.98px)');
        const closeMenu = () => window.bootstrap.Collapse.getOrCreateInstance(menu, { toggle: false }).hide();
        menu.addEventListener('shown.bs.collapse', () => toggle.setAttribute('aria-label', 'Tutup menu navigasi'));
        menu.addEventListener('hidden.bs.collapse', () => toggle.setAttribute('aria-label', 'Buka menu navigasi'));
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && mobile.matches && menu.classList.contains('show')) {
                closeMenu(); toggle.focus();
            }
        });
        document.addEventListener('click', event => {
            if (mobile.matches && menu.classList.contains('show') && !event.target.closest('.main-nav')) closeMenu();
        });
        mobile.addEventListener('change', () => { if (!mobile.matches) closeMenu(); });
    }
}
