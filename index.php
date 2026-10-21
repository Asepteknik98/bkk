<?php
declare(strict_types=1);
require_once __DIR__ . '/app/helpers/public.php';
$pageTitle = 'Beranda'; $activePage = '';
$latestNews = public_query("SELECT id, judul, slug, ringkasan, gambar, published_at, created_at FROM berita WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW()) ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT 6");
$featured = public_query("SELECT id, judul, slug, ringkasan, gambar, published_at, created_at FROM berita WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW()) ORDER BY unggulan DESC, COALESCE(published_at, created_at) DESC, id DESC LIMIT 1")[0] ?? null;
$companions = array_slice(array_values(array_filter($latestNews, static fn(array $row): bool => $row['id'] !== ($featured['id'] ?? null))), 0, 2);
$jobs = public_query("SELECT perusahaan, posisi, slug, lokasi, jenis_pekerjaan, pendidikan, batas_lamaran, deskripsi FROM lowongan WHERE status = 'published' ORDER BY created_at DESC, id DESC LIMIT 3");
$partners = public_query('SELECT nama, jenis, logo, deskripsi FROM mitra WHERE aktif = 1 ORDER BY nama LIMIT 12');
require __DIR__ . '/app/layouts/header.php';
require __DIR__ . '/app/layouts/breaking-news.php';
?>
<main id="konten" class="container portal-container">
    <section class="home-intro"><div><p class="eyebrow">Informasi hari ini, bekal masa depan</p><h1>Temukan peluang.<br><span>Rencanakan langkahmu.</span></h1></div><p>Ruang informasi BKK SMK JAYA BUANA untuk menjelajahi dunia kerja, melanjutkan pendidikan, dan membangun usaha.</p></section>
    <section class="featured-section" aria-labelledby="featured-title">
        <div class="section-heading"><h2 id="featured-title">Sorotan BKK</h2><a href="<?= e(base_url('berita.php')) ?>">Semua berita ↗</a></div>
        <div class="featured-grid">
            <?php if ($featured): ?>
            <article class="lead-story"><img src="<?= e(content_image($featured['gambar'])) ?>" alt="" width="960" height="600" fetchpriority="high"><div class="lead-content"><p class="eyebrow">Berita utama · <?= e(indo_date($featured['published_at'] ?? $featured['created_at'])) ?></p><?php demo_label($featured); ?><h2><a href="<?= e(news_url($featured)) ?>"><?= e($featured['judul']) ?></a></h2><p class="clamp-text"><?= e($featured['ringkasan']) ?></p><a class="text-link" href="<?= e(news_url($featured)) ?>">Baca selengkapnya ↗</a></div></article>
            <?php else: ?>
            <div class="lead-story lead-empty"><span class="lead-overline">BKK / JAYA BUANA</span><div><span class="gold-rule"></span><p class="eyebrow">Ruang informasi siswa &amp; alumni</p><h2>Kabar baru<br>akan hadir di sini.</h2><p>Belum ada berita utama yang diterbitkan. Jelajahi informasi dan kategori program BKK melalui portal ini.</p><a class="button button-gold" href="<?= e(base_url('profil.php')) ?>">Kenali BKK ↗</a></div><span class="lead-footnote">Berita · Karir · Pendidikan</span></div>
            <?php endif; ?>
            <div class="companion-stories"><?php if ($companions): foreach ($companions as $story): news_card($story); endforeach; else: empty_state('Menanti kabar berikutnya', 'Berita pendamping akan tampil setelah informasi baru diterbitkan oleh pengelola.'); endif; ?></div>
        </div>
    </section>
    <section class="program-section" aria-labelledby="program-title"><div class="section-heading"><div><p class="eyebrow">Pilih arah perjalananmu</p><h2 id="program-title">Lima program BKK</h2></div><span class="section-note">Dari persiapan karir hingga studi lanjut</span></div><div class="program-grid">
        <?php $number = 0; foreach (program_categories() as $category): $number++; ?><a class="program-card" href="<?= e(base_url($category['path'])) ?>"><span class="program-top"><span class="program-index">0<?= $number ?></span><span aria-hidden="true">↗</span></span><h3><?= e($category['title']) ?></h3><p><?= e($category['description']) ?></p></a><?php endforeach; ?>
    </div></section>
    <div class="content-with-sidebar"><div class="content-column">
        <section class="section-space" aria-labelledby="jobs-title"><div class="section-heading"><div><p class="eyebrow">Peluang karir</p><h2 id="jobs-title">Lowongan terbaru</h2></div><a href="<?= e(base_url('lowongan-kerja/')) ?>">Semua lowongan ↗</a></div><div class="job-grid"><?php if ($jobs): foreach ($jobs as $job): job_card($job); endforeach; else: empty_state('Belum ada lowongan diterbitkan', 'Periksa kembali halaman ini untuk informasi lowongan dari pengelola BKK.'); endif; ?></div></section>
        <section class="section-space" aria-labelledby="news-title"><div class="section-heading"><div><p class="eyebrow">Kabar dari BKK</p><h2 id="news-title">Berita &amp; kegiatan terbaru</h2></div><a href="<?= e(base_url('berita.php')) ?>">Lihat semua ↗</a></div><div class="news-grid"><?php if ($latestNews): foreach ($latestNews as $story): news_card($story); endforeach; else: empty_state('Belum ada berita diterbitkan'); endif; ?></div></section>
    </div><?php require __DIR__ . '/app/layouts/sidebar.php'; ?></div>
    <section class="partners-section section-space" aria-labelledby="partners-title"><div class="section-heading"><div><p class="eyebrow">Jejaring BKK</p><h2 id="partners-title">Mitra perusahaan &amp; perguruan tinggi</h2></div></div>
        <?php if ($partners): ?><div class="partner-grid"><?php foreach ($partners as $partner): ?><article class="partner-card"><img src="<?= e(content_image($partner['logo'])) ?>" alt="Placeholder atau logo mitra" width="180" height="100" loading="lazy"><?php demo_label($partner); ?><h3><?= e($partner['nama']) ?></h3><p><?= $partner['jenis'] === 'kampus' ? 'Perguruan tinggi' : 'Perusahaan' ?></p></article><?php endforeach; ?></div><?php else: empty_state('Informasi mitra belum tersedia', 'Daftar perusahaan dan perguruan tinggi akan ditampilkan setelah dikonfirmasi oleh pengelola.'); endif; ?>
    </section>
</main>
<?php require __DIR__ . '/app/layouts/footer.php'; ?>
