<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/panel.php';
require_once APP_ROOT . '/app/helpers/validation.php';
$user = require_role('admin', 'staf_bkk');
$q = input_text($_GET, 'q');
if (!valid_text($q, 100, false)) { $q = ''; }
$filter = input_text($_GET, 'status');
$params = []; $where = '1=1';
if ($q !== '') {
    $where .= " AND (b.judul LIKE :judul ESCAPE '!' OR b.slug LIKE :slug ESCAPE '!')";
    $pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q) . '%';
    $params = ['judul' => $pattern, 'slug' => $pattern];
}
if (in_array($filter, ['draft', 'published'], true)) { $where .= ' AND b.status=:status'; $params['status'] = $filter; }
$total = (int) panel_query('SELECT COUNT(*) FROM berita b WHERE ' . $where, $params)->fetchColumn();
$pages = max(1, (int) ceil($total / 10));
$page = min($pages, positive_id($_GET['page'] ?? '1') ?? 1);
$rows = panel_query("SELECT b.*, u.nama AS penulis, (b.status='published' AND (b.published_at IS NULL OR b.published_at<=NOW())) AS is_public FROM berita b LEFT JOIN users u ON u.id=b.penulis_id WHERE " . $where . ' ORDER BY b.updated_at DESC,b.id DESC LIMIT :limit OFFSET :offset', $params + ['limit' => 10, 'offset' => ($page - 1) * 10])->fetchAll();
panel_start($user, 'Kelola Berita', 'berita');
?>
<div class="content-management">
<div class="page-actions"><div><p class="eyebrow">Pengelolaan konten</p><h1>Berita &amp; Kegiatan</h1></div><a class="button" href="<?= e(base_url('panel/berita/tambah.php')) ?>">+ Tambah berita</a></div>
<form method="get" class="filter-form card"><label for="q">Cari berita<input type="search" id="q" name="q" maxlength="100" placeholder="Judul atau slug berita" value="<?= e($q) ?>"></label><label for="status">Status<select id="status" name="status"><option value="">Semua status</option><option value="draft" <?= $filter === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $filter === 'published' ? 'selected' : '' ?>>Published</option></select></label><button type="submit">Cari</button><a class="filter-reset" href="<?= e(base_url('panel/berita/')) ?>">Reset</a></form>
<p><?= $total ?> berita ditemukan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><caption class="sr-only">Daftar berita dan tindakan pengelolaan</caption><thead><tr><th>Judul</th><th>Status</th><th>Penulis</th><th>Publikasi</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><strong><?= e($row['judul']) ?></strong><small><?= e($row['slug']) ?></small><?php if ($row['unggulan']): ?><span class="badge">Unggulan</span><?php endif; ?></td><td><span class="badge <?= $row['status'] === 'published' ? 'published' : '' ?>"><?= e($row['status']) ?></span></td><td><?= e($row['penulis'] ?? 'Pengelola BKK') ?></td><td><?= e($row['published_at'] ?? 'Belum dipublikasikan') ?></td><td><div class="row-actions"><a href="<?= e(base_url('panel/berita/edit.php?id=' . $row['id'])) ?>">Edit</a><?php if ((int) $row['is_public'] === 1): ?><a href="<?= e(base_url('detail-berita.php?slug=' . rawurlencode($row['slug']))) ?>">Lihat publik</a><?php endif; ?><form method="post" action="<?= e(base_url('panel/berita/hapus.php')) ?>" data-confirm="Hapus berita ini? Berkas gambar tetap disimpan di katalog media."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $row['id'] ?>"><button class="danger small-button" type="submit">Hapus</button></form></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="empty-panel">Belum ada berita yang sesuai. Tambah berita atau ubah pencarian.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if ($pages > 1): ?><nav class="pagination" aria-label="Halaman daftar berita"><?php if ($page > 1): ?><a href="<?= e(base_url('panel/berita/?' . http_build_query(['q' => $q, 'status' => $filter, 'page' => $page - 1]))) ?>">← Sebelumnya</a><?php endif; ?><span><?= $page ?> / <?= $pages ?></span><?php if ($page < $pages): ?><a href="<?= e(base_url('panel/berita/?' . http_build_query(['q' => $q, 'status' => $filter, 'page' => $page + 1]))) ?>">Berikutnya →</a><?php endif; ?></nav><?php endif; ?>
</div>
<?php panel_end($user); ?>
