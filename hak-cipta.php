<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/public.php';
$pageTitle='Informasi Hak Cipta';$activePage='';
require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><article class="surface"><h1>Informasi Hak Cipta</h1>
<p>Pemegang hak cipta konten sekolah: <?= e(setting('copyright_owner') ?: 'Belum diisi sekolah') ?>.</p>
<p>Konten yang dibuat sekolah dikelola oleh BKK. Logo, foto, materi, serta informasi dari mitra atau pihak lain tetap mengikuti hak pemilik dan izin penggunaannya masing-masing. Publikasi pada portal tidak otomatis memberikan izin penggunaan ulang.</p>
<p>Untuk permintaan penggunaan konten atau laporan kepemilikan materi, hubungi <?= e(setting('manager_name') ?: 'pengelola yang akan ditetapkan sekolah') ?> melalui <?= e(setting('manager_email') ?: 'kontak pada halaman profil sekolah') ?>. Sertakan tautan materi dan keterangan permintaan.</p>
<p><a href="<?= e(base_url('privasi.php')) ?>">Informasi privasi dan identitas pengelola</a></p></article></main>
<?php require APP_ROOT.'/app/layouts/footer.php'; ?>
