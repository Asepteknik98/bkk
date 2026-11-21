<?php
declare(strict_types=1);

// Development preview only; never trust Host or forwarded headers for this check.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
    http_response_code(403);
    exit('Pratinjau perangkat hanya tersedia melalui localhost.');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
require_once __DIR__.'/app/bootstrap.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'self'; script-src 'self'; frame-src 'self'; img-src 'self' data:; base-uri 'none'; form-action 'self'; object-src 'none'; frame-ancestors 'none'");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pratinjau HP — BKK SMK JAYA BUANA</title>
    <link rel="icon" href="<?= e(asset_url('images/ppks.jpeg')) ?>" type="image/jpeg">
    <link rel="stylesheet" href="<?= e(asset_url('css/preview-hp.css')) ?>?v=<?= (int)filemtime(__DIR__.'/assets/css/preview-hp.css') ?>">
    <script src="<?= e(asset_url('js/preview-hp.js')) ?>?v=<?= (int)filemtime(__DIR__.'/assets/js/preview-hp.js') ?>" defer></script>
</head>
<body data-preview-base="<?= e(base_url()) ?>">
<header class="preview-toolbar">
    <div class="preview-title"><span class="preview-mark" aria-hidden="true">▯</span><div><h1>Pratinjau HP</h1><p>BKK SMK JAYA BUANA</p></div></div>
    <div class="preview-controls">
        <label>Perangkat<select id="preview-device"><option value="iphone">iPhone · 390 × 844</option><option value="android">Android · 412 × 915</option></select></label>
        <label>Ukuran tampilan<select id="preview-scale"><option value="auto">Otomatis</option><option value="1">100%</option></select></label>
        <button type="button" id="preview-refresh" title="Muat ulang halaman">↻ <span>Muat ulang</span></button>
    </div>
    <form id="preview-page-form"><label for="preview-path">Halaman website</label><div class="preview-path-row"><span aria-hidden="true">/</span><input id="preview-path" placeholder="Beranda, atau contoh: lowongan-kerja/" autocomplete="off" spellcheck="false"><button type="submit">Buka</button><button type="button" id="preview-home">Beranda</button></div></form>
</header>
<main class="preview-workspace">
    <p id="preview-message" role="status" aria-live="polite">Memuat pratinjau…</p>
    <div class="preview-stage" id="preview-stage">
        <div class="phone-space" id="phone-space">
            <div class="phone phone--iphone" id="preview-phone" aria-label="Bingkai iPhone">
                <span class="phone-side phone-side-left" aria-hidden="true"></span><span class="phone-side phone-side-right" aria-hidden="true"></span>
                <div class="phone-screen">
                    <div class="phone-status" aria-hidden="true"><strong id="phone-time">09:41</strong><span class="phone-camera"></span><div class="phone-indicators"><svg viewBox="0 0 20 14"><path d="M1 12V9h3v3zm5 0V6h3v6zm5 0V3h3v9zm5 0V0h3v12z"/></svg><svg viewBox="0 0 20 14"><path d="M1 4a14 14 0 0 1 18 0l-2 2A11 11 0 0 0 3 6zm4 4a8 8 0 0 1 10 0l-2 2a5 5 0 0 0-6 0zm3 4 2-2 2 2-2 2z"/></svg><svg viewBox="0 0 28 14"><rect x="1" y="2" width="22" height="10" rx="3" fill="none" stroke="currentColor"/><rect x="3" y="4" width="17" height="6" rx="1"/><path d="M25 5h2v4h-2z"/></svg></div></div>
                    <div class="phone-browser"><span class="phone-lock" aria-hidden="true">♙</span><span id="phone-address">localhost</span><span aria-hidden="true">⋯</span></div>
                    <iframe id="preview-frame" src="<?= e(base_url()) ?>" title="Website BKK dalam layar ponsel" sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-downloads" referrerpolicy="same-origin"></iframe>
                    <div class="phone-home" aria-hidden="true"><span></span></div>
                </div>
            </div>
        </div>
    </div>
    <footer class="preview-footer"><strong id="preview-caption">iPhone · 390 × 844</strong><span>Pratinjau bingkai dan ukuran layar. Hasil di Safari atau Android asli dapat berbeda.</span></footer>
</main>
</body>
</html>
