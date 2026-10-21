<?php if (!defined('APP_ROOT')) { http_response_code(403); exit; } ?>
<footer class="site-footer">
    <div class="container portal-container footer-grid">
        <div><p class="footer-kicker">Bursa Kerja Khusus</p><h2><?= e($siteName) ?></h2><p>Portal informasi karir, pendidikan, dan kewirausahaan untuk siswa serta alumni.</p></div>
        <div><h3>Jelajahi</h3><a href="<?= e(base_url('profil.php')) ?>">Profil BKK</a><a href="<?= e(base_url('berita.php')) ?>">Berita &amp; kegiatan</a><a href="<?= e(base_url('lowongan-kerja/')) ?>">Lowongan kerja</a><a href="<?= e(base_url('tracer-study/')) ?>">Tracer study</a></div>
        <div><h3>Kontak BKK</h3><p><?= e(setting('contact_address', 'Alamat belum diisi oleh pengelola.')) ?></p><p>Email: <?= e(setting('contact_email', 'Belum diisi')) ?><br>Telepon: <?= e(setting('contact_phone', 'Belum diisi')) ?></p></div>
    </div>
    <div class="container portal-container footer-bottom"><span>© <?= date('Y') ?> <?= e($siteName) ?></span><a href="<?= e(base_url('auth/login.php')) ?>">Login Pengelola ↗</a></div>
</footer>
</body>
</html>
