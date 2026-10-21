<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/public.php';
http_response_code(404);
$pageTitle='Halaman tidak ditemukan';$pageDescription='Halaman tidak tersedia. Kembali ke beranda atau cari informasi BKK.';$activePage='404';
require __DIR__.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><section class="surface empty-state"><p class="eyebrow">404</p><h1>Halaman tidak ditemukan</h1><p>Tautan mungkin sudah berubah atau informasi belum diterbitkan.</p><p><a class="button button-navy" href="<?= e(base_url()) ?>">Ke beranda</a> <a class="button button-outline" href="<?= e(base_url('pencarian.php')) ?>">Cari informasi</a></p></section></main>
<?php require __DIR__.'/app/layouts/footer.php'; ?>
