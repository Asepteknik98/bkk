<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/helpers/public.php';
$pageTitle = 'Tracer Study'; $activePage = 'tracer-study/';
require APP_ROOT . '/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Ruang alumni</p><h1>Tracer Study</h1><p>Mengenal perjalanan alumni setelah menyelesaikan pendidikan.</p></div><div><div class="content-column"><section class="surface prose"><h2>Apa itu tracer study?</h2><p>Tracer study merupakan pendataan kegiatan alumni setelah lulus, seperti bekerja, melanjutkan pendidikan, berwirausaha, atau mencari pekerjaan. Informasi tersebut membantu sekolah memahami perjalanan alumni dan kebutuhan pengembangan layanan karir.</p><h2>Privasi alumni</h2><p>Data pribadi respons alumni tidak ditampilkan pada portal publik. Pengisian memerlukan persetujuan pemrosesan data. Untuk koreksi data, hubungi kontak BKK di halaman profil.</p></section><div class="section-space"><a class="button button-navy" href="<?= e(base_url('tracer-study/formulir.php')) ?>">Isi formulir tracer study ↗</a><p class="section-space">Koreksi data: <a href="<?= e(base_url('profil.php')) ?>">Profil dan kontak BKK</a>. Pengiriman tambahan tidak otomatis mengganti respons sebelumnya.</p></div></div></div></main>
<?php require APP_ROOT . '/app/layouts/footer.php'; ?>
