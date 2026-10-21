<?php if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); } ?>
<aside class="sidebar">
    <p class="role-label"><?= $user['role'] === 'admin' ? 'Admin' : 'Staf BKK/Guru BKK' ?></p>
    <nav aria-label="Menu panel">
        <?php foreach (['dashboard' => ['panel/index.php', 'Dashboard'], 'berita' => ['panel/berita/', 'Berita & Kegiatan'], 'media' => ['panel/media/', 'Katalog Media']] as $section => [$path, $label]): ?>
        <a href="<?= e(base_url($path)) ?>" <?= ($activePanel ?? 'dashboard') === $section ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="<?= e(base_url()) ?>">Lihat beranda</a>
    </nav>
</aside>
