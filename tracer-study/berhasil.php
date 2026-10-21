<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/public.php';
$receipt=$_SESSION['tracer_receipt']??null;
if(!$receipt||$receipt['expires']<time()){redirect('tracer-study/');}
$pageTitle='Pengiriman Berhasil';$activePage='tracer-study/';require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><section class="surface prose"><p class="eyebrow">Tracer Study</p><h1>Data berhasil dikirim</h1><p><?= $receipt['repeated']?'Formulir ini sudah tersimpan. Pengiriman ulang tidak membuat catatan ganda.':($receipt['additional']?'Pengiriman tambahan Anda tetap disimpan. Jika tujuannya koreksi data sebelumnya, hubungi pengelola agar catatan dapat diperiksa.':'Terima kasih telah mengisi pendataan alumni BKK SMK JAYA BUANA.') ?></p><p>Data pribadi Anda hanya tersedia bagi pengelola yang berwenang dan tidak ditampilkan di portal publik.</p><h2>Permintaan koreksi data</h2><p>Email BKK: <?= e(setting('contact_email','Belum diisi oleh pengelola')) ?><br>Telepon BKK: <?= e(setting('contact_phone','Belum diisi oleh pengelola')) ?></p><p>Periksa <a href="<?= e(base_url('profil.php')) ?>">profil dan kontak BKK</a> untuk informasi kontak yang tersedia.</p><a class="button button-navy" href="<?= e(base_url('tracer-study/')) ?>">Kembali ke informasi tracer study</a> <a href="<?= e(base_url('tracer-study/formulir.php?baru=1')) ?>">Isi formulir baru</a></section></main>
<?php require APP_ROOT.'/app/layouts/footer.php';?>
