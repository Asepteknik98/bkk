'use strict';

const panelRoot = document.documentElement;
if (document.querySelector('.panel-page')) {
    panelRoot.classList.add('panel-js');
    const accountToggle = document.querySelector('.account-menu-toggle');
    const accountMenu = document.querySelector('#account-menu');
    if (accountToggle && accountMenu) {
        const closeAccountMenu = () => {
            accountMenu.hidden = true;
            accountToggle.setAttribute('aria-expanded', 'false');
        };
        accountToggle.addEventListener('click', () => {
            const open = accountMenu.hidden;
            accountMenu.hidden = !open;
            accountToggle.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', event => {
            if (!event.target.closest('.account-dropdown')) closeAccountMenu();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !accountMenu.hidden) {
                closeAccountMenu();
                accountToggle.focus();
            }
        });
        document.addEventListener('focusin', event => {
            if (!event.target.closest('.account-dropdown')) closeAccountMenu();
        });
    }
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

document.querySelectorAll('.tracer-visual').forEach(chart => {
    const donut = chart.querySelector('.tracer-donut');
    const count = chart.querySelector('.donut-center strong');
    const label = chart.querySelector('.donut-center span');
    const reset = () => {
        count.textContent = donut.dataset.total;
        label.textContent = 'Total respons';
        chart.querySelectorAll('.donut-segment').forEach(segment => segment.classList.remove('is-active', 'is-muted'));
    };
    chart.querySelectorAll('.donut-legend button').forEach(button => {
        const highlight = () => {
            count.textContent = button.dataset.value;
            label.textContent = button.dataset.label;
            chart.querySelectorAll('.donut-segment').forEach(segment => {
                const active = segment.dataset.category === button.dataset.category;
                segment.classList.toggle('is-active', active);
                segment.classList.toggle('is-muted', !active);
            });
        };
        button.addEventListener('mouseenter', highlight);
        button.addEventListener('focus', highlight);
        button.addEventListener('click', highlight);
        button.addEventListener('mouseleave', () => { if (document.activeElement !== button) reset(); });
        button.addEventListener('blur', reset);
    });
});

// Keep long admin lists compact while retaining the server's filters and pagination.
document.querySelectorAll('.panel-content .table-scroll, .panel-content .media-grid').forEach((viewport, index) => {
    const media = viewport.classList.contains('media-grid');
    const table = viewport.querySelector('table');
    const rows = table && !table.querySelector('tbody th') ? Array.from(table.querySelectorAll('tbody > tr')).filter(row => !row.querySelector('[colspan]')) : [];
    const perSlide = 5;
    let slide = 0;
    const totalSlides = Math.ceil(rows.length / perSlide);
    viewport.id ||= `admin-list-${index}`;
    viewport.tabIndex = 0;
    viewport.setAttribute('role', 'region');
    viewport.setAttribute('aria-label', media ? 'Geser katalog media' : 'Daftar data, geser untuk melihat kolom lainnya');
    const controls = document.createElement('nav');
    controls.className = 'admin-list-controls';
    controls.setAttribute('aria-label', 'Kontrol daftar');
    const createButton = (text, label, action) => {
        const button = document.createElement('button');
        button.type = 'button'; button.textContent = text;
        button.setAttribute('aria-label', label);
        button.setAttribute('aria-controls', viewport.id);
        button.addEventListener('click', action);
        controls.append(button);
        return button;
    };
    const scroll = direction => viewport.scrollBy({ left: direction * viewport.clientWidth * .8, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    const left = createButton('?', 'Geser daftar ke kiri', () => scroll(-1));
    const right = createButton('?', 'Geser daftar ke kanan', () => scroll(1));
    const previous = createButton('Sebelumnya', 'Lima baris sebelumnya', () => { slide--; render(); });
    const status = document.createElement('span');
    status.setAttribute('role', 'status'); controls.append(status);
    const next = createButton('Berikutnya', 'Lima baris berikutnya', () => { slide++; render(); });
    const updateScroll = () => {
        const overflow = viewport.scrollWidth - viewport.clientWidth > 2;
        left.hidden = right.hidden = !overflow;
        left.disabled = viewport.scrollLeft <= 2;
        right.disabled = viewport.scrollLeft >= viewport.scrollWidth - viewport.clientWidth - 2;
        controls.hidden = !overflow && totalSlides <= 1;
    };
    const render = () => {
        rows.forEach((row, rowIndex) => { row.hidden = Math.floor(rowIndex / perSlide) !== slide; });
        previous.hidden = next.hidden = status.hidden = totalSlides <= 1;
        previous.disabled = slide === 0;
        next.disabled = slide >= totalSlides - 1;
        status.textContent = `${slide * perSlide + 1}?${Math.min((slide + 1) * perSlide, rows.length)} dari ${rows.length} baris pada halaman ini`;
        updateScroll();
    };
    viewport.after(controls);
    viewport.addEventListener('scroll', updateScroll, { passive:true });
    if (window.ResizeObserver) new ResizeObserver(updateScroll).observe(viewport);
    else window.addEventListener('resize', updateScroll);
    render();
});

// One consistent icon language for management headings, fields, and actions.
if (document.querySelector('.panel-page')) {
    const paths = {
        add:'M12 5v14M5 12h14',
        edit:'m16 3 5 5M3 21l4-1L21 6l-4-4L3 16v5Z',
        delete:'M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7',
        search:'M21 21l-5-5M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0',
        save:'M4 3h13l4 4v14H3V3h1ZM7 3v6h10V3M7 21v-8h10v8',
        reset:'M3 10a9 9 0 1 1 1 8M3 3v7h7',
        back:'M20 12H4m6-6-6 6 6 6',
        next:'M4 12h16m-6-6 6 6-6 6',
        file:'M5 3h9l5 5v13H5V3ZM14 3v6h5M8 13h8M8 17h6',
        user:'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0M4 21v-2a8 8 0 0 1 16 0v2',
        lock:'M6 10V7a6 6 0 0 1 12 0v3M4 10h16v11H4V10ZM12 14v3',
        mail:'M3 5h18v14H3V5Zm0 1 9 7 9-7',
        phone:'M7 3H3c0 10 8 18 18 18v-4l-5-2-2 2a16 16 0 0 1-7-7l2-2-2-5Z',
        calendar:'M5 5h14v16H5V5ZM8 3v4M16 3v4M5 10h14',
        link:'m10 14 4-4M8 16l-2 2a4 4 0 0 1-6-6l5-5a4 4 0 0 1 6 0M16 8l2-2a4 4 0 0 1 6 6l-5 5a4 4 0 0 1-6 0',
        image:'M3 3h18v18H3V3ZM3 17l5-5 4 4 4-6 5 7M8 7h.01',
        chart:'M4 20V10M10 20V4M16 20v-7M2 21h20',
        settings:'M3 6h18M3 12h18M3 18h18M8 3v6M16 9v6M9 15v6',
        briefcase:'M3 7h18v14H3V7ZM8 7V3h8v4M3 12l9 3 9-3',
        school:'m2 9 10-6 10 6-10 6L2 9ZM6 12v6l6 3 6-3v-6',
        location:'M19 9c0 6-7 12-7 12S5 15 5 9a7 7 0 1 1 14 0M14 9a2 2 0 1 1-4 0 2 2 0 0 1 4 0',
        check:'m5 12 4 4L19 6',
        download:'M12 3v12m-5-5 5 5 5-5M4 17v4h16v-4',
        upload:'M12 16V3m-5 5 5-5 5 5M4 17v4h16v-4',
        eye:'M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0'
    };
    const iconFor = text => {
        const rules = [
            [/hapus|nonaktifkan/i,'delete'],[/reset.*password|password|sandi/i,'lock'],
            [/tambah|baru/i,'add'],[/simpan|kirim/i,'save'],[/unggah/i,'upload'],
            [/ekspor|unduh/i,'download'],[/edit|ubah/i,'edit'],[/cari/i,'search'],
            [/reset/i,'reset'],[/batal|kembali|daftar|sebelumnya/i,'back'],[/berikutnya/i,'next'],
            [/lihat|detail/i,'eye'],[/email/i,'mail'],[/telepon|whatsapp|kontak/i,'phone'],
            [/tahun|tanggal|batas|publikasi|dikirim/i,'calendar'],[/website|tautan|url|slug|instagram|facebook|youtube/i,'link'],
            [/gambar|logo|poster|media/i,'image'],[/jurusan|pendidikan|kampus|kuliah|program studi/i,'school'],
            [/lokasi|alamat/i,'location'],[/status|aktif|persetujuan/i,'check'],
            [/perusahaan|pekerjaan|usaha|lowongan|mitra/i,'briefcase'],[/tracer|rekap|respons|jumlah/i,'chart'],
            [/nama|pengguna|akun|username|penulis|nisn/i,'user'],[/pengaturan|role|jenis|kategori|tindakan|aksi/i,'settings']
        ];
        return rules.find(([pattern]) => pattern.test(text))?.[1] || 'file';
    };
    const appendIcon = (element, name) => {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox','0 0 24 24'); svg.setAttribute('fill','none');
        svg.setAttribute('stroke','currentColor'); svg.setAttribute('stroke-width','1.7');
        svg.setAttribute('stroke-linecap','round'); svg.setAttribute('stroke-linejoin','round');
        svg.setAttribute('aria-hidden','true'); svg.setAttribute('focusable','false');
        svg.classList.add('panel-auto-icon');
        const path = document.createElementNS('http://www.w3.org/2000/svg','path');
        path.setAttribute('d',paths[name]); svg.append(path); element.prepend(svg);
    };
    document.querySelectorAll('.panel-content h1, .panel-content h2, .panel-content label, .panel-content th, .panel-content button, .panel-content .button, .panel-content .row-actions a, .panel-content .page-actions a, .panel-content .form-actions a, .panel-content .pagination a, .panel-content .filter-form > a').forEach(element => {
        if (element.querySelector('svg') || element.closest('.donut-legend') || element.classList.contains('sr-only')) return;
        const text = element.cloneNode(true);
        text.querySelectorAll('input,select,textarea,small,.badge').forEach(child => child.remove());
        const label = text.textContent.trim();
        if (!label) return;
        appendIcon(element, iconFor(label));
    });
}
