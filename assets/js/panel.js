'use strict';

const panelRoot = document.documentElement;
if (document.querySelector('.panel-page')) {
    panelRoot.classList.add('panel-js');
    const sidebar = document.querySelector('#panel-sidebar');
    const drawerButton = document.querySelector('.sidebar-open-button');
    const collapseButton = document.querySelector('.sidebar-collapse');
    const themeButton = document.querySelector('.theme-toggle');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const mobile = window.matchMedia('(max-width: 900px)');
    const panelHeader = document.querySelector('.navbar');
    const syncHeaderHeight = () => panelRoot.style.setProperty('--panel-header-height', Math.ceil(panelHeader.getBoundingClientRect().height) + 'px');
    if (window.ResizeObserver) new ResizeObserver(syncHeaderHeight).observe(panelHeader);
    else window.addEventListener('resize', syncHeaderHeight);
    syncHeaderHeight();
    const savePreference = (key, value) => { try { localStorage.setItem(key, value); } catch (_) {} };
    let previousFocus;
    const closeDrawer = () => {
        delete panelRoot.dataset.drawer;
        drawerButton.setAttribute('aria-expanded', 'false');
        backdrop.hidden = true;
        sidebar.removeAttribute('role');
        sidebar.removeAttribute('aria-modal');
        document.querySelectorAll('.navbar,.panel-content,.panel-footer').forEach(el => { el.inert = false; });
        if (mobile.matches && previousFocus) previousFocus.focus();
    };
    drawerButton.addEventListener('click', () => {
        if (panelRoot.dataset.drawer === 'open') { closeDrawer(); return; }
        previousFocus = drawerButton;
        panelRoot.dataset.drawer = 'open';
        drawerButton.setAttribute('aria-expanded', 'true');
        backdrop.hidden = false;
        sidebar.setAttribute('role', 'dialog');
        sidebar.setAttribute('aria-modal', 'true');
        document.querySelectorAll('.navbar,.panel-content,.panel-footer').forEach(el => { el.inert = true; });
        sidebar.querySelector('.sidebar-close').focus();
    });
    backdrop.addEventListener('click', closeDrawer);
    sidebar.querySelector('.sidebar-close').addEventListener('click', closeDrawer);
    sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', () => { if (mobile.matches) closeDrawer(); }));
    mobile.addEventListener('change', closeDrawer);
    document.addEventListener('keydown', event => {
        if (panelRoot.dataset.drawer !== 'open') return;
        if (event.key === 'Escape') { closeDrawer(); return; }
        if (event.key === 'Tab') {
            const controls = Array.from(sidebar.querySelectorAll('a,button')).filter(el => el.getClientRects().length);
            const first = controls[0], last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    const syncCollapse = () => {
        const expanded = panelRoot.dataset.sidebar !== 'collapsed';
        collapseButton.setAttribute('aria-expanded', String(expanded));
        collapseButton.setAttribute('aria-label', expanded ? 'Perkecil sidebar' : 'Perluas sidebar');
        collapseButton.title = collapseButton.getAttribute('aria-label');
    };
    collapseButton.addEventListener('click', () => {
        panelRoot.dataset.sidebar = panelRoot.dataset.sidebar === 'collapsed' ? 'expanded' : 'collapsed';
        savePreference('bkk.panel.sidebar', panelRoot.dataset.sidebar);
        syncCollapse();
    });
    const syncTheme = () => {
        const dark = panelRoot.dataset.theme === 'dark';
        const label = dark ? 'Mode terang' : 'Mode gelap';
        themeButton.querySelector('.theme-label').textContent = label;
        themeButton.setAttribute('aria-label', 'Aktifkan ' + label.toLowerCase());
        themeButton.title = themeButton.getAttribute('aria-label');
        themeButton.setAttribute('aria-pressed', String(dark));
    };
    themeButton.addEventListener('click', () => {
        panelRoot.dataset.theme = panelRoot.dataset.theme === 'dark' ? 'light' : 'dark';
        savePreference('bkk.panel.theme', panelRoot.dataset.theme);
        syncTheme();
    });
    syncCollapse();
    syncTheme();
}
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
