<?php if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); } ?>
<button class="sidebar-backdrop" type="button" aria-label="Tutup menu panel" tabindex="-1" hidden></button>
<aside class="sidebar" id="panel-sidebar" aria-label="Navigasi pengelola">
    <div class="sidebar-heading"><span class="sidebar-wordmark">Ruang<span>Pengelola BKK</span></span><button class="sidebar-collapse icon-button" type="button" aria-controls="panel-sidebar" aria-expanded="true" aria-label="Perkecil sidebar"><?= panel_icon('collapse') ?></button><button class="sidebar-close icon-button" type="button" aria-label="Tutup menu panel"><?= panel_icon('close') ?></button></div>
    <nav aria-label="Menu panel">
        <?php foreach (['dashboard' => ['panel/index.php', 'Dashboard'], 'berita' => ['panel/berita/', 'Berita & Kegiatan'], 'lowongan' => ['panel/lowongan/', 'Lowongan Kerja'], 'lamaran' => ['panel/lamaran/', 'Lamaran Masuk'], 'whatsapp' => ['panel/whatsapp/', 'Pesan WhatsApp'], 'program' => ['panel/program/', 'Program BKK'], 'tracer' => ['panel/tracer-study/', 'Tracer Study'], 'media' => ['panel/media/', 'Katalog Media'], 'mitra' => ['panel/mitra/', 'Mitra'], 'akun' => ['panel/akun/profil.php', 'Akun Pribadi']] as $section => [$path, $label]): ?>
        <a href="<?= e(base_url($path)) ?>" title="<?= e($label) ?>" aria-label="<?= e($label) ?>" <?= ($activePanel ?? 'dashboard') === $section ? 'aria-current="page"' : '' ?>><?= panel_icon($section) ?><span class="sidebar-label"><?= e($label) ?></span></a>
        <?php endforeach; ?>
        <?php if ($user['role'] === 'admin'): foreach (['pengguna'=>'Pengguna','pengaturan'=>'Pengaturan'] as $section=>$label): ?>
        <a href="<?= e(base_url('panel/'.$section.'/')) ?>" title="<?= e($label) ?>" aria-label="<?= e($label) ?>" <?= ($activePanel ?? '') === $section ? 'aria-current="page"' : '' ?>><?= panel_icon($section) ?><span class="sidebar-label"><?= e($label) ?></span></a>
        <?php endforeach; endif; ?>
    </nav>
    <div class="sidebar-bottom">
        <button class="theme-toggle" type="button" aria-pressed="false" title="Aktifkan mode gelap"><span class="theme-icon"><?= panel_icon('moon') ?><?= panel_icon('sun') ?></span><span class="sidebar-label theme-label">Mode gelap</span><span class="theme-switch" aria-hidden="true"></span></button>
        <form action="<?= e(base_url('auth/logout.php')) ?>" method="post"><?= csrf_field() ?><button class="sidebar-logout" type="submit" title="Keluar" aria-label="Keluar"><?= panel_icon('logout') ?><span class="sidebar-label">Keluar</span></button></form>
        <p class="sidebar-caption sidebar-label">BKK SMK JAYA BUANA</p>
    </div>
</aside>
