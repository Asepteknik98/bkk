<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/helpers/public.php';
$pageTitle = 'Lowongan Kerja'; $activePage = 'lowongan-kerja/';
$jobs = public_query("SELECT perusahaan, posisi, slug, lokasi, jenis_pekerjaan, pendidikan, batas_lamaran, deskripsi FROM lowongan WHERE status = 'published' ORDER BY created_at DESC, id DESC LIMIT 30");
require APP_ROOT . '/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Langkah menuju dunia kerja</p><h1>Lowongan Kerja</h1><p>Ringkasan lowongan yang diterbitkan pengelola BKK. Perhatikan batas lamaran pada setiap informasi.</p></div><div class="content-with-sidebar"><div class="content-column"><p class="information-note">Menampilkan maksimal 30 lowongan terbaru. Informasi detail dan pendaftaran belum tersedia.</p><div class="job-grid"><?php if ($jobs): foreach ($jobs as $job): job_card($job); endforeach; else: empty_state('Belum ada lowongan diterbitkan'); endif; ?></div></div><?php require APP_ROOT . '/app/layouts/sidebar.php'; ?></div></main>
<?php require APP_ROOT . '/app/layouts/footer.php'; ?>
