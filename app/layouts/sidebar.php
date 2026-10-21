<?php if (!defined('APP_ROOT')) { http_response_code(403); exit; } ?>
<aside class="portal-sidebar" aria-label="Informasi tambahan">
    <section class="announcement-box">
        <p class="eyebrow">Papan informasi</p><h2>Pengumuman</h2>
        <?php $announcement = setting('public_announcement'); ?>
        <?php if ($announcement): ?><?php demo_label(['isi' => $announcement]); ?><p><?= nl2br(e($announcement)) ?></p>
        <?php else: ?><p>Belum ada pengumuman yang diterbitkan.</p><?php endif; ?>
        <a class="text-link" href="<?= e(base_url('berita.php')) ?>">Lihat berita BKK <span aria-hidden="true">↗</span></a>
    </section>
    <section class="tracer-box">
        <span class="section-number" aria-hidden="true">ALUMNI</span><h2>Setiap perjalanan punya cerita.</h2>
        <p>Kenali tracer study dan pendataan perjalanan alumni setelah lulus.</p>
        <a class="button button-gold" href="<?= e(base_url('tracer-study/')) ?>">Informasi Tracer Study <span aria-hidden="true">↗</span></a>
    </section>
    <section class="quick-links"><h2>Akses cepat</h2><a href="<?= e(base_url('lowongan-kerja/')) ?>">Lowongan kerja <span aria-hidden="true">↗</span></a><a href="<?= e(base_url('kelas-melanjutkan/')) ?>">Kuliah &amp; beasiswa <span aria-hidden="true">↗</span></a><a href="<?= e(base_url('profil.php')) ?>">Profil &amp; kontak BKK <span aria-hidden="true">↗</span></a></section>
</aside>
