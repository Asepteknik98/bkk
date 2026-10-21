<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/middleware/role.php';
$user = require_role('admin', 'staf_bkk');
$pageTitle = 'Dashboard';
require __DIR__ . '/layouts/header.php';
require __DIR__ . '/layouts/navbar.php';
?>
<div class="panel-shell">
<?php require __DIR__ . '/layouts/sidebar.php'; ?>
<main id="konten" class="panel-content">
    <h1>Dashboard</h1>
    <section class="card">
        <h2>Selamat datang, <?= e($user['nama']) ?></h2>
        <p>Anda masuk sebagai <strong><?= $user['role'] === 'admin' ? 'Admin' : 'Staf BKK/Guru BKK' ?></strong>.</p>
        <p>Panel pengelola BKK SMK JAYA BUANA siap digunakan. Modul pengelolaan konten akan tersedia pada tahap berikutnya.</p>
        <?php if ($user['role'] === 'admin'): ?>
            <p>Hak akses Anda mencakup pengelolaan pengguna dan pengaturan saat modul tersebut tersedia.</p>
        <?php else: ?>
            <p>Pengelolaan pengguna dan pengaturan dibatasi untuk Admin.</p>
        <?php endif; ?>
    </section>
</main>
</div>
<?php require __DIR__ . '/layouts/footer.php'; ?>
