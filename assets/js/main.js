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

// Native scrolling also supports touch gestures and keyboard navigation.
document.querySelectorAll('.news-slider').forEach(slider => {
    const controls = slider.previousElementSibling;
    if (!controls || !controls.classList.contains('news-slider-controls')) return;
    const previous = controls.querySelector('.news-slide-prev');
    const next = controls.querySelector('.news-slide-next');
    const update = () => {
        const limit = slider.scrollWidth - slider.clientWidth;
        controls.hidden = limit <= 2;
        previous.disabled = slider.scrollLeft <= 2;
        next.disabled = slider.scrollLeft >= limit - 2;
    };
    const move = direction => {
        const card = slider.querySelector('.news-story-card, .job-card, .partner-card');
        if (!card) return;
        const step = card.getBoundingClientRect().width + parseFloat(getComputedStyle(slider).columnGap || 0);
        slider.scrollBy({ left: direction * step, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    };
    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    slider.addEventListener('scroll', update, { passive: true });
    if (window.ResizeObserver) new ResizeObserver(update).observe(slider);
    else window.addEventListener('resize', update);
    update();
});

// Small, local SVG icons for public navigation and content actions.
if (document.querySelector('.portal-page')) {
    const publicIcons = {
        home:'m3 10 9-7 9 7v11H3V10ZM9 21v-8h6v8',
        user:'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0M4 21v-2a8 8 0 0 1 16 0v2',
        news:'M4 3h16v18H4V3ZM8 7h8M8 11h8M8 15h3M8 18h8',
        job:'M3 7h18v14H3V7ZM8 7V3h8v4M3 12l9 3 9-3',
        school:'m2 9 10-6 10 6-10 6L2 9ZM6 12v6l6 3 6-3v-6',
        chart:'M4 20V10M10 20V4M16 20v-7M2 21h20',
        search:'m21 21-5-5M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0',
        arrow:'M4 12h16m-6-6 6 6-6 6',
        back:'M20 12H4m6-6-6 6 6 6',
        link:'M14 3h7v7M21 3 11 13M10 3H3v18h18v-7',
        mail:'M3 5h18v14H3V5Zm0 1 9 7 9-7',
        phone:'M7 3H3c0 10 8 18 18 18v-4l-5-2-2 2a16 16 0 0 1-7-7l2-2-2-5Z',
        location:'M19 9c0 6-7 12-7 12S5 15 5 9a7 7 0 1 1 14 0M14 9a2 2 0 1 1-4 0 2 2 0 0 1 4 0',
        calendar:'M5 5h14v16H5V5ZM8 3v4M16 3v4M5 10h14',
        lock:'M6 10V7a6 6 0 0 1 12 0v3M4 10h16v11H4V10ZM12 14v3',
        business:'M3 21V5h10v16M13 9h8v12M7 9h2M7 13h2M7 17h2M17 13h1M17 17h1',
        check:'m5 12 4 4L19 6',
        file:'M14 3H5v18h14V8l-5-5ZM14 3v6h5M8 13h8M8 17h6',
        upload:'M12 16V3m-5 5 5-5 5 5M4 15v6h16v-6',
        certificate:'M16 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M9 12l-2 9 5-3 5 3-2-9',
        message:'M4 4h16v12H9l-5 4V4ZM8 8h8M8 12h5',
        send:'m3 3 19 9-19 9 4-9-4-9ZM7 12h15',
        shield:'m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Zm-4 9 3 3 5-6',
        clipboard:'M9 5H5v16h14V5h-4M9 3h6v4H9V3ZM8 12h8M8 16h5'
    };
    const publicIconFor = label => {
        const rules = [[/beranda/i,'home'],[/login|pengelola|privasi/i,'lock'],[/kembali|sebelumnya/i,'back'],[/cari|pencarian/i,'search'],[/email/i,'mail'],[/telepon|whatsapp/i,'phone'],[/alamat|lokasi/i,'location'],[/tahun|tanggal|batas/i,'calendar'],[/website|instagram|facebook|youtube|tautan/i,'link'],[/tracer|alumni|respons/i,'chart'],[/mitra|usaha|entrepreneur|perusahaan/i,'business'],[/kuliah|pendidikan|jurusan|kelas|program|beasiswa/i,'school'],[/lowongan|pekerjaan|karir|posisi/i,'job'],[/profil|nama|nisn|penulis/i,'user'],[/berita|kegiatan|informasi|ringkasan|keterangan/i,'news'],[/kirim|berhasil|setuju/i,'check']];
        return rules.find(([pattern])=>pattern.test(label))?.[1] || 'arrow';
    };
    const addPublicIcon = (element, name) => {
        const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');
        for (const [key,value] of Object.entries({viewBox:'0 0 24 24',fill:'none',stroke:'currentColor','stroke-width':'1.7','stroke-linecap':'round','stroke-linejoin':'round','aria-hidden':'true',focusable:'false'})) svg.setAttribute(key,value);
        svg.classList.add('public-icon');
        const path=document.createElementNS('http://www.w3.org/2000/svg','path');
        path.setAttribute('d',publicIcons[name]); svg.append(path); element.prepend(svg);
    };
    document.querySelectorAll('.main-nav .nav-link, .main-nav .dropdown-item, .manager-login, main h1, main h2, main h3, main label, main .button, main .text-link, main .home-program-link, main .program-cta, main .partner-website, main .section-heading > a, main .pagination-nav a, main .contact-list dt, .site-footer .footer-links a, .site-footer .footer-meta-links a, main button[type="submit"], .application-form legend').forEach(element=>{
        if(element.querySelector('svg') || element.closest('.news-slider-controls') || element.classList.contains('visually-hidden')) return;
        // Card titles often contain links; put the icon inside the link.
        const target=element.matches('h1,h2,h3') && element.querySelector('a') ? element.querySelector('a') : element;
        if(target.querySelector('.public-icon')) return;
        const clone=target.cloneNode(true);
        clone.querySelectorAll('input,select,textarea,small,[aria-hidden="true"]').forEach(child=>child.remove());
        const label=clone.textContent.trim();
        if(label) {
            const name=element.dataset.publicIcon;
            if(element.matches('.application-form label') && !element.classList.contains('application-consent')) {
                const caption=document.createElement('span');
                caption.className='application-label';
                while(element.firstChild && !(element.firstChild.nodeType===1 && element.firstChild.matches('input,textarea,select'))) caption.append(element.firstChild);
                element.prepend(caption);
                addPublicIcon(caption,publicIcons[name] ? name : publicIconFor(label));
            } else {
                addPublicIcon(target,publicIcons[name] ? name : publicIconFor(label));
            }
        }
    });
}

// Public announcement modal with optional hyperlink support from admin text.
const publicAnnouncement = document.getElementById('public-announcement-modal');
if (publicAnnouncement) {
    const dismissAnnouncement = () => {
        publicAnnouncement.hidden = true;
        document.body.classList.remove('announcement-open');
        try { sessionStorage.setItem('public-announcement-dismissed', '1'); } catch (error) { /* no-op */ }
    };
    const shouldShowAnnouncement = () => {
        try {
            return sessionStorage.getItem('public-announcement-dismissed') !== '1';
        } catch (error) {
            return true;
        }
    };
    if (shouldShowAnnouncement()) {
        publicAnnouncement.hidden = false;
        document.body.classList.add('announcement-open');
    }
    publicAnnouncement.querySelectorAll('[data-close-announcement]').forEach(button => {
        button.addEventListener('click', dismissAnnouncement);
    });
    document.addEventListener('keydown', event => {
        if (!publicAnnouncement.hidden && event.key === 'Escape') dismissAnnouncement();
    });
}
