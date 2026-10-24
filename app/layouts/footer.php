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
        <nav class="footer-meta-links" aria-label="Media sosial BKK">
            <?php foreach (social_networks() as $key=>$label): $socialUrl=setting($key); if ($socialUrl && http_url($socialUrl)): ?>
            <a href="<?= e($socialUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($label) ?> (tab baru)" title="<?= e($label) ?>"><?= social_icon($key) ?></a>
            <?php else: ?><span class="social-unavailable" role="link" aria-disabled="true" aria-label="<?= e($label) ?>: tautan belum tersedia" title="<?= e($label) ?>: tautan belum tersedia"><?= social_icon($key) ?></span>
            <?php endif; endforeach; ?>
        </nav>
    </div>
</footer>
<?php
$popupNews = public_popup_news();
$announcement = $popupNews['isi'] ?? '';
?>
<?php if ($announcement): ?>
<?php
$announcementImage = ($popupNews['gambar'] ?? '');
$announcementImageUrl = $announcementImage !== '' ? content_image($announcementImage) : '';
$announcementTitle = $popupNews['judul'];
$announcementWebsite = '';
if (preg_match_all('~(?:https?://|www\.)[^\s<>"\']+~i', $announcement, $websiteMatches)) {
    foreach ($websiteMatches[0] as $candidate) {
        $candidate = rtrim($candidate, '.,;:!?)');
        $candidate = stripos($candidate, 'www.') === 0 ? 'https://' . $candidate : $candidate;
        if (http_url($candidate)) { $announcementWebsite = $candidate; break; }
    }
}
?>
<div class="public-announcement-modal" id="public-announcement-modal" data-site="<?= e(base_url()) ?>" hidden>
    <div class="public-announcement-backdrop" data-close-announcement aria-hidden="true"></div>
    <div class="public-announcement-dialog" role="dialog" aria-modal="true" aria-labelledby="public-announcement-title" aria-describedby="public-announcement-content" tabindex="-1">
        <button class="public-announcement-close" type="button" data-close-announcement aria-label="Tutup pengumuman">&times;</button>
        <div class="public-announcement-poster">
            <?php if ($announcementImageUrl !== '' && $announcementImageUrl !== asset_url('images/placeholder.svg')): ?>
            <img src="<?= e($announcementImageUrl) ?>" alt="Poster: <?= e($announcementTitle) ?>">
            <?php else: ?>
            <div class="public-announcement-poster-placeholder" aria-hidden="true">
                <span class="public-announcement-poster-mark">BKK</span>
                <span class="public-announcement-poster-label">INFORMASI KARIER</span>
                <span class="public-announcement-poster-school"><?= e($siteName ?? 'BKK') ?></span>
                <span class="public-announcement-poster-caption">Informasi penting untuk siswa dan alumni</span>
            </div>
            <?php endif; ?>
        </div>
        <div class="public-announcement-content">
            <p class="public-announcement-eyebrow">Berita &amp; informasi lowongan kerja</p>
            <h2 id="public-announcement-title"><?= e($announcementTitle) ?></h2>
            <div class="public-announcement-body" id="public-announcement-content"><?= render_linked_text($announcement) ?></div>
        <?php $announcementLinkText = 'Lihat detail berita / lowongan'; $announcementLinkUrl = base_url('detail-berita.php?slug='.rawurlencode($popupNews['slug'])); ?>
        <div class="public-announcement-footer">
            <a class="public-announcement-action" href="<?= e($announcementLinkUrl) ?>"><?= e($announcementLinkText) ?><span aria-hidden="true">↗</span></a>
            <?php if ($announcementWebsite !== ''): ?>
            <a class="public-announcement-action public-announcement-website" href="<?= e($announcementWebsite) ?>" target="_blank" rel="noopener noreferrer">Kunjungi website <span aria-hidden="true">&#8599;</span><small><?= e(parse_url($announcementWebsite, PHP_URL_HOST) ?? '') ?> &middot; tab baru</small></a>
            <?php endif; ?>
            <button class="public-announcement-continue" type="button" data-close-announcement>Tutup &amp; lanjutkan ke website</button>
        </div>
        </div>
    </div>
</div>
<?php endif; ?>
</body>
</html>
