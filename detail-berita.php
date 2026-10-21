<?php
declare(strict_types=1);
require_once __DIR__ . '/app/helpers/public.php';
$slug = is_string($_GET['slug'] ?? null) ? $_GET['slug'] : '';
$story = strlen($slug) <= 191 ? (public_query("SELECT b.judul, b.slug, b.ringkasan, b.isi, b.gambar, b.published_at, b.created_at, u.nama AS penulis FROM berita b LEFT JOIN users u ON u.id = b.penulis_id WHERE b.slug = :slug AND b.status = 'published' AND (b.published_at IS NULL OR b.published_at <= NOW()) LIMIT 1", ['slug' => $slug])[0] ?? null) : null;
setting('site_name');
if (!$story) { http_response_code(!empty($GLOBALS['public_database_unavailable']) ? 503 : 404); }
$pageTitle = $story['judul'] ?? 'Berita belum tersedia'; $activePage = 'berita.php';
$pageDescription = $story['ringkasan'] ?? 'Informasi berita BKK SMK JAYA BUANA.';
require __DIR__ . '/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><nav class="breadcrumb-trail" aria-label="Jejak navigasi"><a href="<?= e(base_url()) ?>">Beranda</a><span aria-hidden="true">/</span><a href="<?= e(base_url('berita.php')) ?>">Berita</a><span aria-hidden="true">/</span><span>Detail berita</span></nav><div class="content-with-sidebar"><div class="content-column">
    <?php if ($story): ?><article class="article-detail surface"><p class="eyebrow">Berita &amp; kegiatan</p><?php demo_label($story); ?><h1><?= e($story['judul']) ?></h1><p class="meta"><?= e(indo_date($story['published_at'] ?? $story['created_at'])) ?> · <?= e($story['penulis'] ?? 'Pengelola BKK') ?></p><p class="article-summary"><?= e($story['ringkasan']) ?></p><img class="article-image" src="<?= e(content_image($story['gambar'])) ?>" alt="<?= e($story['judul']) ?>" width="960" height="600"><div class="article-body"><?= plain_text_paragraphs($story['isi']) ?></div></article>
    <?php else: empty_state(!empty($GLOBALS['public_database_unavailable']) ? 'Berita belum dapat dimuat' : 'Berita tidak ditemukan', 'Berita mungkin belum diterbitkan atau tautan sudah tidak tersedia.'); endif; ?>
    <p class="section-space"><a class="text-link" href="<?= e(base_url('berita.php')) ?>">← Kembali ke daftar berita</a></p></div><?php require __DIR__ . '/app/layouts/sidebar.php'; ?></div>
</main>
<?php require __DIR__ . '/app/layouts/footer.php'; ?>
