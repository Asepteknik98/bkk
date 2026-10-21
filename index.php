<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(app_config('name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css')) ?>">
</head>
<body>
<main class="status-card">
    <p class="eyebrow">Bursa Kerja Khusus</p>
    <h1><?= e(app_config('name')) ?></h1>
    <p class="status">Fondasi aplikasi berhasil dijalankan.</p>
    <p>Portal sedang dalam tahap pengembangan. Informasi resmi sekolah akan ditambahkan oleh pengelola.</p>
    <p class="note">Halaman pemeriksaan ini dapat dibuka tanpa koneksi database.</p>
</main>
</body>
</html>
