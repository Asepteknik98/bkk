<?php
if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); }
require_once APP_ROOT.'/app/helpers/public.php';
$sidebarActive=$activePanel??'dashboard';
$sidebarCategory=$categoryFilter??($data['kategori']??'');
$sidebarFile=basename($_SERVER['SCRIPT_FILENAME']??'');
$sidebarPermissionCache=[];
$sidebarCan=static function(string $module) use ($user,&$sidebarPermissionCache): bool {
    return $sidebarPermissionCache[$module]??=user_can($user,$module);
};
$sidebarItem=static function(string $key,string $path,string $label,string $icon,array $requires=[],?bool $active=null) use ($sidebarActive): array {
    return ['key'=>$key,'path'=>$path,'label'=>$label,'icon'=>$icon,'requires'=>$requires,'active'=>$active??($sidebarActive===$key)];
};
$sidebarGroups=[
    'publikasi'=>['label'=>'Publikasi','icon'=>'berita','items'=>[
        $sidebarItem('berita','panel/berita/','Berita & Kegiatan','berita',['berita']),
        $sidebarItem('media','panel/media/','Katalog Media','media',['media']),
    ]],
    'karier'=>['label'=>'Karier & Alumni','icon'=>'lowongan','items'=>[
        $sidebarItem('lowongan','panel/lowongan/','Lowongan Kerja','lowongan',['lowongan']),
        $sidebarItem('hubungan-kerja','panel/hubungan-kerja/','Jenis hubungan kerja','lowongan',['hubungan-kerja']),
        $sidebarItem('lamaran','panel/lamaran/','Lamaran Masuk','lamaran',['lamaran']),
        $sidebarItem('hasil-alumni','panel/hasil-alumni/','Hasil alumni','tracer',['hasil-alumni','tracer-study']),
        $sidebarItem('tracer','panel/tracer-study/','Tracer Study','tracer',['tracer-study']),
        $sidebarItem('whatsapp','panel/whatsapp/','Pesan WhatsApp','whatsapp',['whatsapp','lamaran','tracer-study']),
    ]],
    'bmw'=>['label'=>'Program BMW SAKTI','icon'=>'program','items'=>[
        $sidebarItem('program','panel/program/','Daftar program','program',['program'],$sidebarActive==='program'&&$sidebarCategory===''&&$sidebarFile!=='deskripsi.php'),
        $sidebarItem('deskripsi-bmw','panel/program/deskripsi.php','Deskripsi BMW Sakti','program',['program'],$sidebarActive==='program'&&$sidebarFile==='deskripsi.php'),
    ]],
    'pembelajaran'=>['label'=>'Siswa & Pembelajaran','icon'=>'peserta','items'=>[
        $sidebarItem('peserta','panel/peserta/','Data siswa / peserta','peserta',['peserta']),
        $sidebarItem('kelas-industri','panel/kelas-industri/','Kelas Industri / Pertemuan','program',['kelas-industri','peserta']),
        $sidebarItem('capaian','panel/capaian-pembelajaran/','Rapor Pembelajaran','capaian',['capaian-pembelajaran','peserta']),
        $sidebarItem('absensi','panel/absensi/','Absensi Kegiatan','absensi',['absensi','peserta']),
        $sidebarItem('pemateri','panel/pemateri/','Pemateri Kelas','pemateri',['pemateri']),
    ]],
    'pkl'=>['label'=>'PKL & Perjalanan','icon'=>'calendar','items'=>[
        $sidebarItem('pkl','panel/pkl/','Praktik Kerja Lapangan','peserta',['pkl','peserta']),
        $sidebarItem('biaya-perjalanan','panel/biaya-perjalanan/','Perjalanan dinas & biaya','location',['biaya-perjalanan','pkl','peserta']),
    ]],
    'kemitraan'=>['label'=>'Mitra & Kerja Sama','icon'=>'mitra','items'=>[
        $sidebarItem('mitra','panel/mitra/','Mitra','mitra',['mitra'],$sidebarActive==='mitra'&&$sidebarFile!=='kampus.php'),
        $sidebarItem('kampus','panel/mitra/kampus.php','Kampus Dalam Negeri','program',['mitra'],$sidebarActive==='mitra'&&$sidebarFile==='kampus.php'),
        $sidebarItem('mou','panel/mou/','Riwayat MoU','certificate',['mou','mitra']),
        $sidebarItem('sarasehan','panel/sarasehan/','Sarasehan Industri','calendar',['sarasehan','mitra']),
        $sidebarItem('ide-industri','panel/ide-industri/','Kotak Ide Industri','message',['ide-industri','mitra']),
    ]],
];
foreach(['kelas_internasional','entrepreneur','kelas_melanjutkan','ppks','kelas_industri'] as $sidebarProgramKey){
    $sidebarProgramInfo=program_categories()[$sidebarProgramKey];
    $sidebarGroups['bmw']['items'][]=$sidebarItem('program-'.$sidebarProgramKey,'panel/program/?kategori='.$sidebarProgramKey,$sidebarProgramInfo['title'],$sidebarProgramKey==='entrepreneur'?'mitra':'program',['program'],$sidebarActive==='program'&&$sidebarCategory===$sidebarProgramKey&&$sidebarFile!=='deskripsi.php');
}
$sidebarGroups['bmw']['items'][]=$sidebarItem('program-minat','panel/program-minat/','Minat & pertanyaan','message',['program-minat']);
if($user['role']==='admin'){
    $sidebarGroups['administrasi']=['label'=>'Administrasi','icon'=>'pengaturan','items'=>[
        $sidebarItem('pengguna','panel/pengguna/','Pengguna','pengguna'),
        $sidebarItem('pengaturan','panel/pengaturan/','Pengaturan','pengaturan'),
    ]];
}
$newApplications=(int)($newApplications??0);
$sidebarRenderLink=static function(array $item) use ($newApplications): void {
    $unseen=$item['key']==='lamaran'?(int)($newApplications??0):0;
    $accessibleLabel=$item['label'].($unseen>0?' - '.$unseen.' lamaran belum dilihat':'');
    ?>
    <a href="<?= e(base_url($item['path'])) ?>" title="<?= e($accessibleLabel) ?>" aria-label="<?= e($accessibleLabel) ?>" <?= $item['active']?'aria-current="page"':'' ?>><?= panel_icon($item['icon']) ?><span class="sidebar-label"><?= e($item['label']) ?></span><?php if($unseen>0): ?><span class="inbox-new-count sidebar-label" aria-hidden="true"><?= $unseen>99?'99+':$unseen ?></span><?php endif; ?></a>
    <?php
};
?>
<button class="sidebar-backdrop" type="button" aria-label="Tutup menu panel" tabindex="-1" hidden></button>
<aside class="sidebar" id="panel-sidebar" aria-label="Navigasi pengelola">
    <div class="sidebar-heading"><span class="sidebar-wordmark">Panel BKK<span>Ruang pengelola</span></span><button class="sidebar-collapse icon-button" type="button" aria-controls="panel-sidebar" aria-expanded="true" aria-label="Perkecil sidebar"><?= panel_icon('collapse') ?></button><button class="sidebar-close icon-button" type="button" aria-label="Tutup menu panel"><?= panel_icon('close') ?></button></div>
    <nav aria-label="Menu panel">
        <?php $sidebarRenderLink($sidebarItem('dashboard','panel/index.php','Dashboard','dashboard')); ?>
        <?php foreach($sidebarGroups as $sidebarGroupKey=>$sidebarGroup):
            $sidebarVisibleItems=array_values(array_filter($sidebarGroup['items'],static function(array $item) use ($sidebarCan): bool {
                foreach($item['requires'] as $module){if(!$sidebarCan($module)){return false;}}return true;
            }));
            if(!$sidebarVisibleItems){continue;}
            $sidebarGroupActive=(bool)array_filter($sidebarVisibleItems,static fn(array $item):bool=>$item['active']);
        ?>
        <details class="sidebar-programs sidebar-menu-group" data-sidebar-group="<?= e($sidebarGroupKey) ?>" data-active="<?= $sidebarGroupActive?'true':'false' ?>" <?= $sidebarGroupActive?'open':'' ?>>
            <summary title="<?= e($sidebarGroup['label']) ?>" aria-controls="sidebar-group-<?= e($sidebarGroupKey) ?>"><?= panel_icon($sidebarGroup['icon']) ?><span class="sidebar-label"><?= e($sidebarGroup['label']) ?></span><span class="sidebar-program-caret" aria-hidden="true"><?= panel_icon('down') ?></span></summary>
            <div id="sidebar-group-<?= e($sidebarGroupKey) ?>"><?php foreach($sidebarVisibleItems as $item){$sidebarRenderLink($item);} ?></div>
        </details>
        <?php endforeach; ?>
        <?php $sidebarRenderLink($sidebarItem('akun','panel/akun/profil.php','Akun Pribadi','akun')); ?>
    </nav>
    <div class="sidebar-bottom">
        <button class="theme-toggle" type="button" aria-pressed="false" title="Aktifkan mode gelap"><span class="theme-icon"><?= panel_icon('moon') ?><?= panel_icon('sun') ?></span><span class="sidebar-label theme-label">Mode gelap</span><span class="theme-switch" aria-hidden="true"></span></button>
        <form action="<?= e(base_url('auth/logout.php')) ?>" method="post"><?= csrf_field() ?><button class="sidebar-logout" type="submit" title="Keluar" aria-label="Keluar"><?= panel_icon('logout') ?><span class="sidebar-label">Keluar</span></button></form>
        <p class="sidebar-caption sidebar-label">BKK SMK JAYA BUANA</p>
    </div>
</aside>
