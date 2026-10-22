<?php
if (!defined('APP_ROOT')) { http_response_code(403); exit; }

/** Ikon lokal dengan path tetap; tidak menerima markup dari pengguna. */
function panel_icon(string $name): string
{
    $paths = [
        'dashboard'=>'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'berita'=>'<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h3M8 18h8"/>',
        'lowongan'=>'<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V4h8v3M3 12c5 4 13 4 18 0M12 12v4"/>',
        'program'=>'<path d="m2 9 10-6 10 6-10 6L2 9Zm4 3v6c4 3 8 3 12 0v-6M22 9v8"/>',
        'tracer'=>'<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>',
        'media'=>'<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 5-5 4 4 4-6 5 7"/>',
        'mitra'=>'<path d="M3 21V5h10v16M13 9h8v12M7 9h2M7 13h2M7 17h2M17 13h1M17 17h1M2 21h20"/>',
        'akun'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'pengguna'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5"/>',
        'pengaturan'=>'<path d="M4 6h16M4 12h16M4 18h16"/><circle cx="8" cy="6" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="9" cy="18" r="2"/>',
        'home'=>'<path d="m3 10 9-7 9 7v11H3V10ZM9 21v-8h6v8"/>',
        'view'=>'<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'lamaran'=>'<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'logout'=>'<path d="M10 4H4v16h6M9 12h12m-4-4 4 4-4 4"/>',
        'collapse'=>'<path d="m14 6-6 6 6 6"/>',
        'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
        'moon'=>'<path d="M20 14A8 8 0 0 1 10 4a8 8 0 1 0 10 10Z"/>',
        'sun'=>'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1 1M18 18l1 1M5 19l1-1M18 6l1-1"/>',
        'close'=>'<path d="m6 6 12 12M6 18 18 6"/>',
    ];
    return '<svg class="panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.($paths[$name]??$paths['dashboard']).'</svg>';
}
