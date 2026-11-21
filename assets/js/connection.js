'use strict';
(() => {
    if (document.getElementById('bkk-connection')) return;
    const notice = document.createElement('aside');
    notice.id = 'bkk-connection';
    notice.className = 'bkk-connection';
    notice.hidden = true;
    notice.setAttribute('aria-label', 'Status koneksi');
    // Static markup only; no user data is inserted as HTML.
    notice.innerHTML = '<svg class="bkk-connection__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 8a15 15 0 0 1 18 0M6 12a10 10 0 0 1 12 0M9 16a5 5 0 0 1 6 0"/><circle cx="12" cy="20" r="1"/></svg><div class="bkk-connection__text" role="status" aria-live="polite" aria-atomic="true"><strong></strong><span></span></div><button type="button" hidden>Muat ulang</button>';
    document.body.appendChild(notice);
    const title = notice.querySelector('strong');
    const description = notice.querySelector('.bkk-connection__text span');
    const reload = notice.querySelector('button');
    let wasOffline = false;
    const update = () => {
        const offline = navigator.onLine === false;
        if (!offline && !wasOffline) return;
        if (offline) wasOffline = true;
        notice.hidden = false;
        notice.dataset.state = offline ? 'offline' : 'online';
        title.textContent = offline ? 'Koneksi terputus' : 'Jaringan kembali tersedia';
        description.textContent = offline
            ? 'Anda sedang offline. Periksa jaringan sebelum mengirim atau menyimpan data.'
            : 'Silakan muat ulang. Salin isian yang belum disimpan terlebih dahulu.';
        reload.hidden = offline;
    };
    reload.addEventListener('click', () => {
        if (navigator.onLine === false) { update(); return; }
        // Navigate with GET, avoiding resubmission of a previous POST.
        window.location.assign(window.location.href);
    });
    window.addEventListener('offline', update);
    window.addEventListener('online', update);
    window.addEventListener('pageshow', update);
    update();
})();
