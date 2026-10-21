<?php if (!defined('APP_ROOT')) { http_response_code(403); exit; } ?>
<nav class="main-nav navbar navbar-expand-xl" aria-label="Navigasi utama">
    <div class="header-navigation">
        <span class="mobile-nav-label">Jelajahi portal</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#public-menu" aria-controls="public-menu" aria-expanded="false" aria-label="Buka menu navigasi"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg><span>Menu</span></button>
        <div class="collapse navbar-collapse" id="public-menu">
            <ul class="navbar-nav">
            <?php foreach (['Beranda' => '', 'Profil BKK' => 'profil.php', 'Berita' => 'berita.php', 'Lowongan Kerja' => 'lowongan-kerja/', 'Tracer Study' => 'tracer-study/'] as $label => $path): ?>
                <li class="nav-item"><a class="nav-link<?= ($activePage ?? '') === $path ? ' active' : '' ?>" <?= ($activePage ?? '') === $path ? 'aria-current="page"' : '' ?> href="<?= e(base_url($path)) ?>"><?= e($label) ?></a></li>
            <?php endforeach; ?>
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle<?= in_array($activePage ?? '', array_column(program_categories(), 'path'), true) ? ' active' : '' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">Program BKK</button>
                    <ul class="dropdown-menu">
                    <?php foreach (program_categories() as $category): ?>
                        <li><a class="dropdown-item" <?= ($activePage ?? '') === $category['path'] ? 'aria-current="page"' : '' ?> href="<?= e(base_url($category['path'])) ?>"><?= e($category['title']) ?></a></li>
                    <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link<?= ($activePage ?? '') === 'pencarian.php' ? ' active' : '' ?>" <?= ($activePage ?? '') === 'pencarian.php' ? 'aria-current="page"' : '' ?> href="<?= e(base_url('pencarian.php')) ?>">Pencarian</a></li>
            </ul>
        </div>
    </div>
</nav>
