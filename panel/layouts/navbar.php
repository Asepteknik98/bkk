<?php if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); } ?>
<header class="navbar">
    <a class="brand" href="<?= e(base_url('panel/index.php')) ?>"><?= e(app_config('name')) ?></a>
    <div class="navbar-account">
        <span><?= e($user['nama']) ?></span>
        <form action="<?= e(base_url('auth/logout.php')) ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit">Keluar</button>
        </form>
    </div>
</header>
