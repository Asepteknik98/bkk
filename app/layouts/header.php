<?php
if (!defined('APP_ROOT')) { http_response_code(403); exit; }
$siteName = setting('site_name', (string) app_config('name'));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e(meta_description($pageDescription ?? (($pageTitle ?? 'Beranda') . ' — ' . $siteName . '. Informasi karir, pendidikan, dan kewirausahaan bagi siswa serta alumni.'))) ?>">
    <title><?= e(($pageTitle ?? 'Beranda') . ' — ' . $siteName) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('css/responsive.css')) ?>">
    <script src="<?= e(asset_url('vendor/bootstrap/bootstrap.bundle.min.js')) ?>" defer></script>
    <script src="<?= e(asset_url('js/main.js')) ?>" defer></script>
    <noscript><style>.navbar-collapse.collapse{display:block}.main-nav .dropdown-menu{display:block;position:static}.navbar-toggler,.dropdown-toggle{display:none!important}</style></noscript>
</head>
<body>
<a class="skip-link" href="#konten">Langsung ke konten</a>
<div class="topbar"><div class="container portal-container"><span>Portal Bursa Kerja Khusus</span><a href="<?= e(base_url('auth/login.php')) ?>">Login Pengelola <span aria-hidden="true">↗</span></a></div></div>
<header class="masthead container portal-container">
    <a class="site-brand" href="<?= e(base_url()) ?>">
        <?php $logo = setting('bkk_logo'); if ($logo && content_image($logo) !== asset_url('images/placeholder.svg')): ?>
            <img class="brand-logo" src="<?= e(content_image($logo)) ?>" alt="Logo BKK" width="66" height="66">
        <?php else: ?><span class="brand-monogram" aria-hidden="true">BKK<span>JAYA BUANA</span></span><?php endif; ?>
        <span><span class="brand-kicker">Bursa Kerja Khusus</span><span class="brand-title"><?= e($siteName) ?></span><span class="brand-caption"><?= e(setting('school_name', 'SMK JAYA BUANA')) ?></span><span class="brand-caption">Informasi karir · Pendidikan · Kewirausahaan</span></span>
    </a>
    <?php $schoolLogo = setting('school_logo'); if ($schoolLogo && content_image($schoolLogo) !== asset_url('images/placeholder.svg')): ?><img class="brand-logo" src="<?= e(content_image($schoolLogo)) ?>" alt="Logo sekolah" width="66" height="66"><?php endif; ?>
    <form class="header-search" action="<?= e(base_url('pencarian.php')) ?>" method="get" role="search">
        <label class="visually-hidden" for="portal-search">Cari berita, lowongan, dan program</label>
        <input id="portal-search" type="search" name="q" maxlength="100" placeholder="Cari berita, lowongan, dan program…" value="<?= e($searchTerm ?? '') ?>">
        <button type="submit" aria-label="Cari informasi"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" stroke="currentColor" stroke-width="2"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="2"/></svg></button>
    </form>
</header>
<?php require __DIR__ . '/navbar.php'; ?>
<?php if (!empty($GLOBALS['public_database_unavailable'])): ?>
<div class="container portal-container"><p class="service-notice" role="status">Informasi belum dapat dimuat saat ini. Silakan coba kembali nanti atau hubungi pengelola BKK.</p></div>
<?php endif; ?>
