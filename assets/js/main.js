'use strict';
// Menu tetap dapat digunakan bila JavaScript Bootstrap gagal dimuat.
if (!window.bootstrap) {
    document.querySelectorAll('.navbar-collapse, .dropdown-menu').forEach((menu) => {
        menu.classList.add('fallback-menu');
    });
    document.querySelectorAll('.navbar-toggler, .dropdown-toggle').forEach((button) => {
        button.hidden = true;
    });
}
