<?php if (!defined('APP_ROOT')) { http_response_code(403); exit; } ?>
<footer class="site-footer site-footer-compact">
    <div class="container portal-container footer-compact-main">
        <div class="footer-identity">
            <a class="footer-brand" href="<?= e(base_url()) ?>">BKK SMKS JAYA BUANA</a>
        </div>
        <nav class="footer-links" aria-label="Navigasi footer">
            <a href="<?= e(base_url('profil.php')) ?>">Profil &amp; kontak</a>
            <a href="<?= e(base_url('berita.php')) ?>">Berita</a>
            <a href="<?= e(base_url('lowongan-kerja/')) ?>">Lowongan</a>
            <a href="<?= e(base_url('tracer-study/')) ?>">Tracer study</a>
        </nav>
    </div>
    <div class="container portal-container footer-compact-bottom">
        <span>&copy; BKK SMKS JAYA BUANA 2026</span>
        <div class="footer-meta-links">
            <?php foreach (['social_instagram'=>'Instagram','social_facebook'=>'Facebook','social_youtube'=>'YouTube'] as $key=>$label): $socialUrl=setting($key); if ($socialUrl && http_url($socialUrl)): ?>
            <a href="<?= e($socialUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a>
            <?php endif; endforeach; ?>
        </div>
    </div>
<?php $announcement = setting('public_announcement'); ?>
<?php if ($announcement): ?>
<div class="public-announcement-modal" id="public-announcement-modal" hidden>
    <div class="public-announcement-backdrop" data-close-announcement aria-hidden="true"></div>
    <div class="public-announcement-dialog" role="dialog" aria-modal="true" aria-labelledby="public-announcement-title">
        <button class="public-announcement-close" type="button" data-close-announcement aria-label="Tutup pengumuman">&times;</button>
        <p class="eyebrow">Papan informasi</p>
        <h2 id="public-announcement-title">Pengumuman</h2>
        <div class="public-announcement-body"><?= render_linked_text($announcement) ?></div>
        <?php $announcementLinkText = setting('public_announcement_link_text'); $announcementLinkUrl = setting('public_announcement_link_url'); ?>
        <?php if ($announcementLinkText !== '' && http_url($announcementLinkUrl)): ?>
        <a class="public-announcement-action" href="<?= e($announcementLinkUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e($announcementLinkText) ?><span aria-hidden="true">↗</span></a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
</footer>
</body>
</html>
