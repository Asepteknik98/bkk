<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/public.php';
$pageTitle='Informasi Privasi';$activePage='';
require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><article class="surface">
<h1>Informasi Privasi</h1>
<p>Portal BKK memproses data siswa, peserta, pelamar, dan alumni untuk layanan karir sekolah, pendaftaran lowongan, pendataan PKL, absensi, serta evaluasi kegiatan dan tracer study. Formulir menjelaskan data dan persetujuan yang diperlukan; persetujuan informasi WhatsApp bersifat opsional.</p>
<h2>Akses dan penggunaan data</h2><p>Halaman informasi publik dapat dibuka tanpa login. Respons tracer, kontak, berkas lamaran, dan data peserta tidak tersedia untuk pengunjung umum. Admin mengatur izin Staf BKK menurut tugasnya. Dokumen MoU dan informasi biaya perjalanan merupakan data internal dan tidak boleh diunggah sebagai media publik.</p>
<h2>Tautan formulir alumni</h2><p>Tautan pribadi hanya membuka formulir alumni yang dituju, memiliki batas waktu, dapat dicabut, dan berlaku untuk satu pengiriman. Tautan tidak memberikan akses dashboard. Jaga kerahasiaannya dan hubungi pengelola jika tautan diterima oleh orang yang keliru.</p>
<h2>Penyimpanan dan layanan pihak lain</h2><p>Portal menggunakan cookie sesi untuk login, pengamanan formulir, dan tautan pribadi. Preferensi tampilan panel disimpan pada perangkat. Tautan WhatsApp atau situs mitra membawa Anda ke layanan yang memiliki kebijakan privasinya sendiri. Pengelola perlu memastikan perangkat, cadangan data, dan akses server ikut terlindungi.</p>
<p>Masa simpan: <?= e(setting('privacy_retention') ?: 'Belum diisi sekolah. Hubungi pengelola untuk informasi masa simpan dan penghapusan data.') ?></p>
<h2>Kontak pengelola</h2><p>Untuk pertanyaan, koreksi, permintaan penghapusan sesuai keperluan layanan sekolah, atau berhenti menerima informasi WhatsApp, hubungi:</p>
<dl><?php foreach(['manager_name'=>'Penanggung jawab','manager_unit'=>'Unit pengelola','manager_address'=>'Alamat','manager_email'=>'Email','manager_phone'=>'Telepon'] as $key=>$label): ?><dt><?= e($label) ?></dt><dd><?= e(setting($key) ?: 'Belum diisi sekolah') ?></dd><?php endforeach; ?></dl>
</article></main>
<?php require APP_ROOT.'/app/layouts/footer.php'; ?>
