<?php
if (!defined('APP_ROOT')) { http_response_code(403); exit; }
$category = program_categories()[$programCategory];
$pageTitle = $category['title']; $activePage = $category['path'];
$programs = public_query("SELECT judul, slug, ringkasan, gambar FROM program WHERE kategori = :kategori AND status = 'published' ORDER BY created_at DESC, id DESC LIMIT 30", ['kategori' => $programCategory]);
require __DIR__ . '/header.php';
?>
<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Program BKK / <?= e($category['short']) ?></p><h1><?= e($pageTitle) ?></h1><p><?= e($category['description']) ?></p></div><div class="content-with-sidebar"><div class="content-column"><p class="information-note">Ringkasan maksimal 30 program terbaru. Informasi detail dan pendaftaran belum tersedia.</p><div class="news-grid"><?php if ($programs): foreach ($programs as $program): ?><article class="news-card"><img src="<?= e(content_image($program['gambar'])) ?>" alt="" width="640" height="400" loading="lazy"><div class="news-card-body"><?php demo_label($program); ?><h2><?= e($program['judul']) ?></h2><p><?= e($program['ringkasan']) ?></p></div></article><?php endforeach; else: empty_state('Belum ada program diterbitkan'); endif; ?></div></div><?php require __DIR__ . '/sidebar.php'; ?></div></main>
<?php require __DIR__ . '/footer.php'; ?>
