<?php if (!defined('APP_ROOT')) { http_response_code(403); exit; } ?>
<footer class="site-footer site-footer-compact">
    <div class="container portal-container footer-compact-main">
        <a class="footer-brand" href="<?= e(base_url()) ?>"><?= e($siteName) ?></a>
        <nav class="footer-links" aria-label="Navigasi footer">
            <a href="<?= e(base_url('profil.php')) ?>">Profil &amp; kontak</a>
            <a href="<?= e(base_url('berita.php')) ?>">Berita</a>
            <a href="<?= e(base_url('lowongan-kerja/')) ?>">Lowongan</a>
            <a href="<?= e(base_url('tracer-study/')) ?>">Tracer study</a>
        </nav>
    </div>
    <div class="container portal-container footer-compact-bottom">
        <span>? <?= date('Y') ?> <?= e($siteName) ?></span>
        <div class="footer-meta-links">
            <?php foreach (['social_instagram'=>'Instagram','social_facebook'=>'Facebook','social_youtube'=>'YouTube'] as $key=>$label): $socialUrl=setting($key); if ($socialUrl && http_url($socialUrl)): ?>
            <a href="<?= e($socialUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a>
            <?php endif; endforeach; ?>
            <a href="<?= e(base_url('auth/login.php')) ?>">Login Pengelola <span aria-hidden="true">?</span></a>
        </div>
    </div>
</footer>
</body>
</html>
