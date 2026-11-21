'use strict';
(() => {
    const base = new URL(document.body.dataset.previewBase, location.href);
    const frame = document.getElementById('preview-frame');
    const phone = document.getElementById('preview-phone');
    const space = document.getElementById('phone-space');
    const stage = document.getElementById('preview-stage');
    const device = document.getElementById('preview-device');
    const scale = document.getElementById('preview-scale');
    const path = document.getElementById('preview-path');
    const message = document.getElementById('preview-message');
    const devices = { iphone: { width:390, height:844, label:'iPhone' }, android: { width:412, height:915, label:'Android' } };
    const say = (text, error = false) => { message.textContent = text; message.dataset.error = String(error); };
    const fit = () => {
        const size = devices[device.value];
        const width = size.width + 24, height = size.height + 24;
        const css = getComputedStyle(stage);
        const availableWidth = stage.clientWidth - parseFloat(css.paddingLeft) - parseFloat(css.paddingRight);
        const availableHeight = stage.clientHeight - parseFloat(css.paddingTop) - parseFloat(css.paddingBottom);
        const ratio = scale.value === '1' ? 1 : Math.min(1, Math.max(0.15, Math.min(availableWidth / width, availableHeight / height)));
        phone.className = `phone phone--${device.value}`;
        phone.setAttribute('aria-label', `Bingkai ${size.label}`);
        phone.style.setProperty('--screen-width', `${size.width}px`);
        phone.style.setProperty('--screen-height', `${size.height}px`);
        phone.style.transform = `scale(${ratio})`;
        space.style.width = `${width * ratio}px`;
        space.style.height = `${height * ratio}px`;
        document.getElementById('preview-caption').textContent = `${size.label} · ${size.width} × ${size.height} · ${Math.round(ratio * 100)}%`;
    };
    const insideApp = url => url.origin === base.origin && url.pathname.startsWith(base.pathname)
        && url.pathname !== location.pathname;
    const open = value => {
        try {
            const relative = value.trim();
            if (/^[a-z][a-z0-9+.-]*:|^\/|\\/i.test(relative)) throw new Error();
            const url = new URL(relative || './', base);
            if (!insideApp(url)) throw new Error();
            frame.src = url.href;
            path.value = url.pathname.slice(base.pathname.length) + url.search + url.hash;
            say('Memuat halaman…');
        } catch { say('Masukkan halaman di dalam website, misalnya berita.php atau lowongan-kerja/.', true); }
    };
    document.getElementById('preview-page-form').addEventListener('submit', event => { event.preventDefault(); open(path.value); });
    document.getElementById('preview-home').addEventListener('click', () => open(''));
    document.getElementById('preview-refresh').addEventListener('click', () => {
        try { frame.contentWindow.location.reload(); say('Memuat ulang halaman…'); }
        catch { open(path.value); }
    });
    frame.addEventListener('load', () => {
        try {
            const url = new URL(frame.contentWindow.location.href);
            if (!insideApp(url)) throw new Error();
            path.value = url.pathname.slice(base.pathname.length) + url.search + url.hash;
            document.getElementById('phone-address').textContent = url.host + url.pathname;
            say('Pratinjau siap. Gunakan menu website di dalam layar HP.');
        } catch { say('Halaman ini tidak dapat ditampilkan dalam bingkai. Gunakan halaman website lokal.', true); }
    });
    device.addEventListener('change', fit);
    scale.addEventListener('change', fit);
    new ResizeObserver(fit).observe(stage);
    const time = () => { document.getElementById('phone-time').textContent = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' }).replace('.', ':'); };
    time(); setInterval(time, 60000);
    const query = new URLSearchParams(location.search);
    if (Object.hasOwn(devices, query.get('perangkat'))) device.value = query.get('perangkat');
    if (base.origin !== location.origin) {
        say('Buka pratinjau melalui alamat yang sama dengan base URL website.', true);
        frame.removeAttribute('src');
    } else if (query.has('halaman')) open(query.get('halaman'));
    fit();
})();
