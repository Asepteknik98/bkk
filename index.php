<?php
declare(strict_types=1);
require_once __DIR__ . '/app/helpers/public.php';
$pageTitle = 'Beranda'; $activePage = '';
$latestNews = public_query("SELECT id, judul, slug, ringkasan, gambar, published_at, created_at FROM berita WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW()) ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT 6");
$jobs = public_query("SELECT perusahaan, posisi, slug, lokasi, jenis_pekerjaan, pendidikan, batas_lamaran, deskripsi FROM lowongan WHERE status = 'published' ORDER BY created_at DESC, id DESC LIMIT 3");
$partners = public_query('SELECT nama, jenis, logo, website, deskripsi FROM mitra WHERE aktif = 1 ORDER BY nama LIMIT 12');
require __DIR__ . '/app/layouts/header.php';
require __DIR__ . '/app/layouts/breaking-news.php';
?>
<main id="konten" class="container portal-container">
    <section class="home-intro"><div><p class="eyebrow">Informasi hari ini, bekal masa depan</p><h1>Temukan peluang.<br><span>Rencanakan langkahmu.</span></h1></div><div class="home-intro-content"><p>Ruang informasi <?= e($siteName) ?> untuk menjelajahi dunia kerja, melanjutkan pendidikan, dan membangun usaha.</p><div class="home-actions"><a class="button button-navy" href="<?= e(base_url('lowongan-kerja/')) ?>">Cari lowongan <span aria-hidden="true">↗</span></a><a class="button button-outline" href="<?= e(base_url('tracer-study/formulir.php')) ?>">Isi tracer study <span aria-hidden="true">↗</span></a></div><a class="home-program-link" href="#program-title">Jelajahi lima program BKK <span aria-hidden="true">↓</span></a></div></section>
    <section class="program-section<?= is_file(__DIR__ . '/assets/images/batik-sekolah.png') ? ' program-section-batik' : '' ?>" aria-labelledby="program-title"><div class="section-heading"><div><p class="eyebrow">Pilih arah perjalananmu</p><h2 id="program-title">Lima program BKK</h2></div><span class="section-note">Dari persiapan karir hingga studi lanjut</span></div><div class="program-grid">
        <?php $number = 0; foreach (program_categories() as $category): $number++; ?><a class="program-card" href="<?= e(base_url($category['path'])) ?>"><span class="program-top"><span class="program-index">0<?= $number ?></span><span aria-hidden="true">↗</span></span><h3><?= e($category['title']) ?></h3><p><?= e($category['description']) ?></p></a><?php endforeach; ?>
    </div></section>
    <div class="content-with-sidebar"><div class="content-column">
        <section class="section-space" aria-labelledby="jobs-title"><div class="section-heading"><div><p class="eyebrow">Peluang karir</p><h2 id="jobs-title">Lowongan terbaru</h2></div><a href="<?= e(base_url('lowongan-kerja/')) ?>">Semua lowongan ↗</a></div><div class="job-grid"><?php if ($jobs): foreach ($jobs as $job): job_card($job); endforeach; else: empty_state('Belum ada lowongan diterbitkan', 'Periksa kembali halaman ini untuk informasi lowongan dari pengelola BKK.'); endif; ?></div></section>
        <section class="section-space" aria-labelledby="news-title"><div class="section-heading"><div><p class="eyebrow">Kabar dari BKK</p><h2 id="news-title">Berita &amp; kegiatan terbaru</h2></div><a href="<?= e(base_url('berita.php')) ?>">Lihat semua ↗</a></div><div class="news-grid"><?php if ($latestNews): foreach ($latestNews as $story): news_card($story); endforeach; else: empty_state('Belum ada berita diterbitkan'); endif; ?></div></section>
    </div><?php require __DIR__ . '/app/layouts/sidebar.php'; ?></div>
    <section class="partners-section section-space" aria-labelledby="partners-title"><div class="section-heading"><div><p class="eyebrow">Jejaring BKK</p><h2 id="partners-title">Mitra perusahaan &amp; perguruan tinggi</h2></div></div>
        <?php if ($partners): ?><div class="partner-grid"><?php foreach ($partners as $partner): ?><article class="partner-card"><img src="<?= e(content_image($partner['logo'])) ?>" alt="<?= e('Mitra ' . $partner['nama']) ?>" width="180" height="100" loading="lazy"><?php demo_label($partner); ?><h3><?= e($partner['nama']) ?></h3><p><?= $partner['jenis'] === 'kampus' ? 'Perguruan tinggi' : 'Perusahaan' ?></p><?php if ($partner['deskripsi']): ?><p><?= nl2br(e($partner['deskripsi'])) ?></p><?php endif; ?><?php if ($partner['website'] && http_url($partner['website'])): ?><a href="<?= e($partner['website']) ?>" target="_blank" rel="noopener noreferrer">Website mitra ↗</a><?php endif; ?></article><?php endforeach; ?></div><?php else: empty_state('Informasi mitra belum tersedia', 'Daftar perusahaan dan perguruan tinggi akan ditampilkan setelah dikonfirmasi oleh pengelola.'); endif; ?>
    </section>
</main>
<?php require __DIR__ . '/app/layouts/footer.php'; ?>
