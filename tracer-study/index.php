<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/helpers/public.php';
$pageTitle = 'Tracer Study'; $activePage = 'tracer-study/';
require APP_ROOT . '/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Ruang alumni</p><h1>Tracer Study</h1><p>Mengenal perjalanan alumni setelah menyelesaikan pendidikan.</p></div><div class="content-with-sidebar"><div class="content-column"><section class="surface prose"><h2>Apa itu tracer study?</h2><p>Tracer study merupakan pendataan kegiatan alumni setelah lulus, seperti bekerja, melanjutkan pendidikan, berwirausaha, atau mencari pekerjaan. Informasi tersebut membantu sekolah memahami perjalanan alumni dan kebutuhan pengembangan layanan karir.</p><h2>Privasi alumni</h2><p>Data pribadi respons alumni tidak ditampilkan pada portal publik. Pengisian nantinya memerlukan persetujuan pemrosesan data.</p></section><div class="section-space"><?php empty_state('Formulir pendataan belum tersedia', 'Informasi pengisian tracer study akan diumumkan oleh pengelola BKK setelah layanan dibuka.'); ?></div></div><?php require APP_ROOT . '/app/layouts/sidebar.php'; ?></div></main>
<?php require APP_ROOT . '/app/layouts/footer.php'; ?>
