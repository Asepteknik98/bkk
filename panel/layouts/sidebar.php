<?php if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); } ?>
<aside class="sidebar">
    <p class="role-label"><?= $user['role'] === 'admin' ? 'Admin' : 'Staf BKK/Guru BKK' ?></p>
    <nav aria-label="Menu panel">
        <a href="<?= e(base_url('panel/index.php')) ?>" aria-current="page">Dashboard</a>
        <a href="<?= e(base_url()) ?>">Lihat beranda</a>
    </nav>
</aside>
