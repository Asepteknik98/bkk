<?php if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); } ?>
<?php require_once APP_ROOT.'/app/helpers/public.php'; ?>
<button class="sidebar-backdrop" type="button" aria-label="Tutup menu panel" tabindex="-1" hidden></button>
<aside class="sidebar" id="panel-sidebar" aria-label="Navigasi pengelola">
    <div class="sidebar-heading"><span class="sidebar-wordmark">Panel BKK<span>Ruang pengelola</span></span><button class="sidebar-collapse icon-button" type="button" aria-controls="panel-sidebar" aria-expanded="true" aria-label="Perkecil sidebar"><?= panel_icon('collapse') ?></button><button class="sidebar-close icon-button" type="button" aria-label="Tutup menu panel"><?= panel_icon('close') ?></button></div>
    <nav aria-label="Menu panel">
        <?php foreach (['dashboard' => ['panel/index.php', 'Dashboard'], 'berita' => ['panel/berita/', 'Berita & Kegiatan'], 'lowongan' => ['panel/lowongan/', 'Lowongan Kerja'], 'lamaran' => ['panel/lamaran/', 'Lamaran Masuk'], 'whatsapp' => ['panel/whatsapp/', 'Pesan WhatsApp'], 'program' => ['panel/program/', 'BMW SAKTI'], 'tracer' => ['panel/tracer-study/', 'Tracer Study'], 'media' => ['panel/media/', 'Katalog Media'], 'mitra' => ['panel/mitra/', 'Mitra'], 'akun' => ['panel/akun/profil.php', 'Akun Pribadi']] as $section => [$path, $label]): ?>
        <?php if($section!=='program'): $menuLabel=$label.($section==='lamaran'&&($newApplications??0)>0?' — '.$newApplications.' lamaran belum dilihat':''); ?><a href="<?= e(base_url($path)) ?>" title="<?= e($menuLabel) ?>" aria-label="<?= e($menuLabel) ?>" <?= ($activePanel ?? 'dashboard') === $section ? 'aria-current="page"' : '' ?>><?= panel_icon($section) ?><span class="sidebar-label"><?= e($label) ?></span><?php if($section==='lamaran'&&($newApplications??0)>0): ?><span class="inbox-new-count sidebar-label" aria-hidden="true"><?= $newApplications>99?'99+':$newApplications ?></span><?php endif; ?></a><?php endif;?>
        <?php if($section==='program'): $selectedCategory=$categoryFilter??($data['kategori']??''); ?>
        <?php $bmwActive=($activePanel??'')==='program'; ?>
        <details class="sidebar-programs" data-sidebar-group="bmw" data-active="<?= $bmwActive?'true':'false' ?>" <?= $bmwActive?'open':'' ?>><summary title="BMW SAKTI"><?= panel_icon('program') ?><span class="sidebar-label">BMW SAKTI</span><span class="sidebar-program-caret" aria-hidden="true"><?= panel_icon('down') ?></span></summary><div>
        <?php foreach(['kelas_internasional','entrepreneur','kelas_melanjutkan','ppks','kelas_industri'] as $programKey):$programInfo=program_categories()[$programKey];?><a href="<?= e(base_url('panel/program/?kategori='.$programKey)) ?>" title="<?= e($programInfo['title']) ?>" <?= ($activePanel??'')==='program'&&$selectedCategory===$programKey?'aria-current="page"':'' ?>><?= panel_icon($programKey==='entrepreneur'?'mitra':'program') ?><span class="sidebar-label"><?= e($programInfo['title']) ?></span></a><?php endforeach;?>
        </div></details>
        <?php $masterActive=in_array($activePanel??'',['peserta','capaian','absensi','pemateri','program-minat'],true); ?>
        <details class="sidebar-programs" data-sidebar-group="master" data-active="<?= $masterActive?'true':'false' ?>" <?= $masterActive?'open':'' ?>><summary title="Master Data"><?= panel_icon('master') ?><span class="sidebar-label">Master Data</span><span class="sidebar-program-caret" aria-hidden="true"><?= panel_icon('down') ?></span></summary><div>
        <?php foreach(['peserta'=>['panel/peserta/','Data Peserta'],'capaian'=>['panel/capaian-pembelajaran/','Capaian Pembelajaran'],'absensi'=>['panel/absensi/','Absensi Kegiatan'],'pemateri'=>['panel/pemateri/','Pemateri Kelas']] as $module=>[$modulePath,$moduleLabel]): ?>
        <a href="<?= e(base_url($modulePath)) ?>" title="<?= e($moduleLabel) ?>" <?= ($activePanel??'')===$module?'aria-current="page"':'' ?>><?= panel_icon($module) ?><span class="sidebar-label"><?= e($moduleLabel) ?></span></a>
        <?php endforeach; ?>
        <a href="<?= e(base_url('panel/program-minat/')) ?>" title="Minat dan pertanyaan" <?= ($activePanel??'')==='program-minat'?'aria-current="page"':'' ?>><?= panel_icon('message') ?><span class="sidebar-label">Minat &amp; pertanyaan</span></a></div></details>
        <?php endif;?>
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
