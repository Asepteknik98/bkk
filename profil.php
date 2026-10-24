<?php
declare(strict_types=1);
require_once __DIR__ . '/app/helpers/public.php';
$pageTitle = 'Profil BKK'; $activePage = 'profil.php';
require __DIR__ . '/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main profile-page"><div class="page-heading"><p class="eyebrow">Mengenal BKK</p><h1>Profil BKK</h1><p><?= e(setting('school_name', 'SMK JAYA BUANA')) ?></p></div><div><div class="content-column">
    <section class="surface prose"><h2><?= e(setting('site_name', 'BKK SMK JAYA BUANA')) ?></h2><p>Portal ini menyediakan ruang informasi mengenai lowongan kerja, pengembangan karir, studi lanjut, kewirausahaan, dan program internasional bagi siswa serta alumni.</p>
    <?php $profile = setting('profile_description'); if ($profile): ?><?php demo_label(['isi' => $profile]); ?><p><?= nl2br(e($profile)) ?></p><?php else: ?><p class="muted">Profil resmi, struktur pengurus, dan layanan BKK belum dilengkapi oleh pengelola.</p><?php endif; ?></section>
    <section class="surface section-space"><p class="eyebrow">Hubungi pengelola</p><h2>Kontak BKK</h2><dl class="contact-list"><dt>Alamat</dt><dd><?= e(setting('contact_address', 'Belum diisi oleh pengelola')) ?></dd><dt>Email</dt><dd><?= e(setting('contact_email', 'Belum diisi oleh pengelola')) ?></dd><dt>Telepon</dt><dd><?= e(setting('contact_phone', 'Belum diisi oleh pengelola')) ?></dd></dl><p class="muted">Informasi kontak yang belum tersedia akan diperbarui oleh pengelola sekolah.</p></section>
    <section class="surface section-space profile-social-section"><h2>Media sosial BKK</h2><nav class="profile-social-links" aria-label="Media sosial BKK">
    <?php foreach(social_networks() as $key=>$label): $socialUrl=setting($key); ?>
        <?php if($socialUrl!==''&&http_url($socialUrl)): ?><a href="<?= e($socialUrl) ?>" target="_blank" rel="noopener noreferrer" title="<?= e($label) ?> (tab baru)"><?= social_icon($key) ?><span><?= e($label) ?></span></a>
        <?php else: ?><span class="social-unavailable" role="link" aria-disabled="true" title="<?= e($label) ?>: tautan belum tersedia"><?= social_icon($key) ?><span><?= e($label) ?></span></span><?php endif; ?>
    <?php endforeach; ?></nav></section>
    </div></div>
</main>
<?php require __DIR__ . '/app/layouts/footer.php'; ?>
