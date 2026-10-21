<?php
if (!defined('APP_ROOT')) { http_response_code(403); exit; }
$searchTerm = isset($isSearch) && is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
if (strlen($searchTerm) > 400) { $searchTerm = ''; }
$rawPage = $_GET['page'] ?? '1';
$page = is_string($rawPage) && ctype_digit($rawPage) ? max(1, min(100000, (int) $rawPage)) : 1;
$params = [];
$where = "status = 'published' AND (published_at IS NULL OR published_at <= NOW())";
if ($searchTerm !== '') {
    $where .= " AND (judul LIKE :judul ESCAPE '!' OR ringkasan LIKE :ringkasan ESCAPE '!')";
    $pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $searchTerm) . '%';
    $params = ['judul' => $pattern, 'ringkasan' => $pattern];
}
$total = (int) (public_query('SELECT COUNT(*) AS total FROM berita WHERE ' . $where, $params)[0]['total'] ?? 0);
$pages = max(1, (int) ceil($total / 9)); $page = min($page, $pages);
$rows = public_query('SELECT judul, slug, ringkasan, gambar, published_at, created_at FROM berita WHERE ' . $where . ' ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT :limit OFFSET :offset', $params + ['limit' => 9, 'offset' => ($page - 1) * 9]);
require __DIR__ . '/header.php';
?>
<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Portal informasi BKK</p><h1><?= e($pageTitle) ?></h1><p><?= isset($isSearch) ? 'Temukan berita dan kegiatan yang telah diterbitkan.' : 'Kabar, informasi, dan kegiatan terbaru dari pengelola BKK.' ?></p></div>
    <?php if (isset($isSearch)): ?><form class="search-form surface" action="<?= e(base_url('pencarian.php')) ?>" method="get" role="search"><label for="news-query">Kata kunci berita</label><div><input id="news-query" name="q" type="search" maxlength="100" value="<?= e($searchTerm) ?>" placeholder="Judul atau ringkasan berita"><button class="button button-navy" type="submit">Cari berita</button></div></form><?php endif; ?>
    <div class="content-with-sidebar"><div class="content-column"><?php if ($searchTerm !== ''): ?><p class="search-result"><?= $total ?> hasil untuk <strong>“<?= e($searchTerm) ?>”</strong></p><?php endif; ?><div class="news-grid"><?php if ($rows): foreach ($rows as $story): news_card($story); endforeach; else: empty_state($searchTerm !== '' ? 'Berita tidak ditemukan' : 'Belum ada berita diterbitkan', $searchTerm !== '' ? 'Coba kata kunci lain untuk mencari judul atau ringkasan berita.' : 'Berita dan kegiatan akan tampil setelah diterbitkan oleh pengelola.'); endif; ?></div>
    <?php if ($pages > 1): ?><nav class="pagination-nav" aria-label="Halaman berita"><?php if ($page > 1): ?><a class="button button-outline" href="<?= e(base_url($activePage . '?' . http_build_query(['q' => $searchTerm, 'page' => $page - 1]))) ?>">← Sebelumnya</a><?php endif; ?><span>Halaman <?= $page ?> dari <?= $pages ?></span><?php if ($page < $pages): ?><a class="button button-outline" href="<?= e(base_url($activePage . '?' . http_build_query(['q' => $searchTerm, 'page' => $page + 1]))) ?>">Berikutnya →</a><?php endif; ?></nav><?php endif; ?>
    </div><?php require __DIR__ . '/sidebar.php'; ?></div>
</main>
<?php require __DIR__ . '/footer.php'; ?>
