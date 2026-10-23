<?php if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); } ?>
<?php require_once APP_ROOT . '/app/helpers/accounts.php'; require_once APP_ROOT . '/app/helpers/public.php'; $logos = site_logos(); ?>
<header class="navbar">
    <button class="sidebar-open-button icon-button" type="button" aria-controls="panel-sidebar" aria-expanded="false" aria-label="Buka menu panel"><?= panel_icon('menu') ?></button>
    <a class="brand" href="<?= e(base_url('panel/index.php')) ?>"><span class="identity-logos"><img src="<?= e($logos['school_logo']) ?>" alt="Logo SMK JAYA BUANA" width="48" height="48"><img src="<?= e($logos['bkk_logo']) ?>" alt="Logo Pusat Pengembangan Karir Peserta" width="48" height="48"></span><span><?= e(app_config('name')) ?></span></a>
    <div class="navbar-account">
        <div class="account-dropdown">
            <button class="account-avatar account-menu-toggle" type="button" aria-label="Menu akun" aria-expanded="false" aria-controls="account-menu"><?php $profilePhoto=account_photo_url($user); if($profilePhoto): ?><img class="account-profile-photo" src="<?= e($profilePhoto) ?>" alt="" width="32" height="32"><?php else: ?><?= panel_icon('akun') ?><?php endif; ?></button>
            <div class="account-menu" id="account-menu" hidden>
                <a href="<?= e(base_url('panel/akun/profil.php')) ?>"><?= panel_icon('akun') ?>Profil akun</a>
                <form action="<?= e(base_url('auth/logout.php')) ?>" method="post"><?= csrf_field() ?><button class="account-menu-logout" type="submit"><?= panel_icon('logout') ?>Keluar</button></form>
            </div>
        </div>
        <a class="account-link" href="<?= e(base_url('panel/akun/profil.php')) ?>"><strong><?= e($user['nama']) ?></strong><small><?= $user['role']==='admin'?'Administrator':'Pengguna' ?></small></a>
    </div>
</header>
