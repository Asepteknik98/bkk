<?php if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); } ?>
<?php require_once APP_ROOT . '/app/helpers/public.php'; $logos = site_logos(); ?>
<header class="navbar">
    <button class="sidebar-open-button icon-button" type="button" aria-controls="panel-sidebar" aria-expanded="false" aria-label="Buka menu panel"><?= panel_icon('menu') ?></button>
    <a class="brand" href="<?= e(base_url('panel/index.php')) ?>"><span class="identity-logos"><img src="<?= e($logos['school_logo']) ?>" alt="Logo SMK JAYA BUANA" width="48" height="48"><img src="<?= e($logos['bkk_logo']) ?>" alt="Logo Pusat Pengembangan Karir Siswa" width="48" height="48"></span><span><?= e(app_config('name')) ?></span></a>
    <div class="navbar-account">
        <span class="account-avatar" aria-hidden="true"><?= panel_icon('akun') ?></span>
        <a class="account-link" href="<?= e(base_url('panel/akun/profil.php')) ?>"><strong><?= e($user['nama']) ?></strong><small><?= $user['role']==='admin'?'Administrator':'Staf BKK' ?></small></a>
    </div>
</header>
