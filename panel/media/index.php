<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/news.php';
$user = require_role('admin', 'staf_bkk');
$q = input_text($_GET, 'q');
if (!valid_text($q, 100, false)) { $q = ''; }
$params = []; $where = '1=1';
if ($q !== '') { $where .= " AND nama_asli LIKE :q ESCAPE '!'"; $params['q'] = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q) . '%'; }
$total = (int) panel_query('SELECT COUNT(*) FROM media WHERE ' . $where, $params)->fetchColumn();
$pages = max(1, (int) ceil($total / 10)); $page = min($pages, positive_id($_GET['page'] ?? '1') ?? 1);
$rows = panel_query('SELECT * FROM media WHERE ' . $where . ' ORDER BY id DESC LIMIT :limit OFFSET :offset', $params + ['limit' => 10, 'offset' => ($page - 1) * 10])->fetchAll();
panel_start($user, 'Katalog Media', 'media');
?>
<div class="media-catalog"><div class="page-actions"><div><h1><?= panel_icon('media') ?> Katalog Media</h1></div><a href="<?= e(base_url('panel/berita/')) ?>">Kelola berita →</a></div>
<div class="media-tools"><form class="editor-form card media-upload" action="<?= e(base_url('panel/media/upload.php')) ?>" method="post" enctype="multipart/form-data"><?= csrf_field() ?><h2><?= panel_icon('upload') ?> Unggah berkas</h2><label for="berkas"><?= panel_icon('media') ?> Gambar atau dokumen</label><input id="berkas" name="berkas" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" required><small>JPG/PNG/WebP: maksimal 5 MB. PDF: maksimal 10 MB.</small><div><button type="submit"><?= panel_icon('upload') ?> Unggah ke media</button></div></form>
<form class="filter-form card" method="get"><label for="media-q"><span><?= panel_icon('search') ?> Cari nama berkas</span><input id="media-q" name="q" maxlength="100" value="<?= e($q) ?>"></label><button type="submit"><?= panel_icon('search') ?> Cari</button><a href="<?= e(base_url('panel/media/')) ?>"><?= panel_icon('reset') ?> Reset</a></form>
</div><p class="media-count"><?= $total ?> berkas · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll media-sheet" data-vertical-list tabindex="0" role="region" aria-label="Daftar berkas media"><table>
<thead><tr><th scope="col"><span><?= panel_icon('media') ?> Berkas</span></th><th scope="col"><span><?= panel_icon('master') ?> Jenis / ukuran</span></th><th scope="col"><span><?= panel_icon('pengaturan') ?> Tindakan</span></th></tr></thead>
<tbody><?php foreach ($rows as $media): $exists = managed_upload_path($media['path']); ?>
<tr><td><div class="media-file"><div class="media-thumb"><?php if ($exists && media_is_image($media)): ?><img src="<?= e(base_url($media['path'])) ?>" alt="" loading="lazy" width="44" height="44"><?php else: ?><?= panel_icon('media') ?><?php endif; ?></div><div><strong><?= e($media['nama_asli']) ?></strong><small>#<?= (int) $media['id'] ?><?php if (!$exists): ?> &middot; Berkas tidak tersedia<?php endif; ?></small></div></div></td>
<td><span><?= e($media['mime_type']) ?></span><small><?= number_format($media['ukuran_bytes'] / 1024, 1, ',', '.') ?> KB<?php if ($media['lebar']): ?> &middot; <?= (int) $media['lebar'] ?> &times; <?= (int) $media['tinggi'] ?> px<?php endif; ?></small></td>
<td><div class="row-actions"><a class="media-edit" href="<?= e(base_url('panel/media/edit.php?id=' . $media['id'])) ?>"><?= panel_icon('edit') ?> Edit</a><?php if ($exists): ?><a href="<?= e(base_url('panel/media/unduh.php?id=' . $media['id'])) ?>"><?= panel_icon('download') ?> Unduh</a><?php endif; ?><form method="post" action="<?= e(base_url('panel/media/hapus.php')) ?>" data-confirm="Hapus berkas ini dari media? Berkas yang masih digunakan akan ditolak."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $media['id'] ?>"><button class="danger small-button" type="submit"><?= panel_icon('trash') ?> Hapus</button></form></div></td></tr>
<?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="3" class="empty-panel">Belum ada media yang sesuai. Unggah berkas atau ubah pencarian.</td></tr><?php endif; ?></tbody></table></div>
<?php if ($pages > 1): $pageStart = max(1, min($page - 4, $pages - 9)); $pageEnd = min($pages, $pageStart + 9); ?>
<nav class="pagination media-pagination" aria-label="Halaman katalog media">
<?php if ($page > 1): ?><a rel="prev" href="<?= e(base_url('panel/media/?' . http_build_query(['q' => $q, 'page' => $page - 1]))) ?>">Sebelumnya</a><?php endif; ?>
<?php for ($number = $pageStart; $number <= $pageEnd; $number++): ?><a href="<?= e(base_url('panel/media/?' . http_build_query(['q' => $q, 'page' => $number]))) ?>" aria-label="Halaman <?= $number ?>" <?= $number === $page ? 'aria-current="page"' : '' ?>><?= $number ?></a><?php endfor; ?>
<?php if ($page < $pages): ?><a rel="next" href="<?= e(base_url('panel/media/?' . http_build_query(['q' => $q, 'page' => $page + 1]))) ?>">Berikutnya</a><?php endif; ?>
</nav><?php endif; ?>
</div>
<?php panel_end($user); ?>
