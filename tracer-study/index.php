<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/helpers/public.php';
$pageTitle = 'Tracer Study'; $activePage = 'tracer-study/';
require APP_ROOT . '/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main tracer-page">
    <div class="page-heading">
        <p class="eyebrow">Ruang alumni</p><h1>Tracer Study</h1>
        <p>Mengenal perjalanan alumni setelah menyelesaikan pendidikan.</p>
    </div>
    <section class="surface tracer-intro">
        <div class="tracer-info-grid">
            <div><h2>Apa itu tracer study?</h2><p>Pendataan kegiatan alumni setelah lulus: bekerja, kuliah, berwirausaha, atau mencari pekerjaan. Informasi ini membantu sekolah mengembangkan layanan karir.</p></div>
            <div><h2>Privasi alumni</h2><p>Data pribadi tidak ditampilkan di portal publik. Pengisian memerlukan persetujuan pemrosesan data. Koreksi data dapat diajukan melalui kontak BKK.</p></div>
        </div>
        <div class="tracer-intro-actions">
            <a class="button button-navy" href="<?= e(base_url('tracer-study/formulir.php')) ?>">Isi formulir tracer study <span aria-hidden="true">&rarr;</span></a>
            <p>Koreksi data: <a href="<?= e(base_url('profil.php')) ?>">Profil &amp; kontak BKK</a>. Pengiriman tambahan tidak otomatis mengganti respons sebelumnya.</p>
        </div>
    </section>
</main>
<?php require APP_ROOT . '/app/layouts/footer.php'; ?>
