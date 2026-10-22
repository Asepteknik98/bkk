<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/news.php';
$user = require_role('admin', 'staf_bkk');
$q = input_text($_GET, 'q');
if (!valid_text($q, 100, false)) { $q = ''; }
$params = []; $where = '1=1';
if ($q !== '') { $where .= " AND nama_asli LIKE :q ESCAPE '!'"; $params['q'] = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q) . '%'; }
$total = (int) panel_query('SELECT COUNT(*) FROM media WHERE ' . $where, $params)->fetchColumn();
$pages = max(1, (int) ceil($total / 12)); $page = min($pages, positive_id($_GET['page'] ?? '1') ?? 1);
$rows = panel_query('SELECT * FROM media WHERE ' . $where . ' ORDER BY id DESC LIMIT :limit OFFSET :offset', $params + ['limit' => 12, 'offset' => ($page - 1) * 12])->fetchAll();
panel_start($user, 'Katalog Media', 'media');
?>
<div class="media-catalog"><div class="page-actions"><div><p class="eyebrow">Gambar dan dokumen bersama</p><h1>Katalog Media</h1></div><a href="<?= e(base_url('panel/berita/')) ?>">Kelola berita →</a></div>
<div class="media-tools"><form class="editor-form card media-upload" action="<?= e(base_url('panel/media/upload.php')) ?>" method="post" enctype="multipart/form-data"><?= csrf_field() ?><h2>Unggah berkas</h2><label for="berkas">Gambar atau dokumen</label><input id="berkas" name="berkas" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" required><small>JPG/PNG/WebP maksimal 5 MB; PDF maksimal 10 MB. Berkas dapat digunakan kembali oleh konten lain.</small><div><button type="submit">Unggah ke media</button></div></form>
<form class="filter-form card" method="get"><label for="media-q">Cari nama berkas<input id="media-q" name="q" maxlength="100" value="<?= e($q) ?>"></label><button type="submit">Cari</button><a href="<?= e(base_url('panel/media/')) ?>">Reset</a></form>
</div><p class="media-count"><?= $total ?> berkas · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="media-grid"><?php foreach ($rows as $media): $exists = managed_upload_path($media['path']); ?>
<article class="media-card"><div class="media-preview"><?php if ($exists && media_is_image($media)): ?><img src="<?= e(base_url($media['path'])) ?>" alt="<?= e($media['nama_asli']) ?>" loading="lazy" width="300" height="180"><?php else: ?><span><?= $media['mime_type'] === 'application/pdf' ? 'PDF' : 'BERKAS' ?></span><?php endif; ?></div><div class="media-body"><h2>#<?= $media['id'] ?> — <?= e($media['nama_asli']) ?></h2><p><?= e($media['mime_type']) ?> · <?= number_format($media['ukuran_bytes'] / 1024, 1, ',', '.') ?> KB<?php if ($media['lebar']): ?><br><?= $media['lebar'] ?> × <?= $media['tinggi'] ?> piksel<?php endif; ?></p><?php if (!$exists): ?><p class="muted">Berkas fisik tidak tersedia atau path tidak valid.</p><?php endif; ?><div class="row-actions"><?php if ($exists): ?><a href="<?= e(base_url('panel/media/unduh.php?id=' . $media['id'])) ?>">Unduh</a><?php endif; ?><form method="post" action="<?= e(base_url('panel/media/hapus.php')) ?>" data-confirm="Hapus berkas ini dari media? Berkas yang masih digunakan akan ditolak."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $media['id'] ?>"><button class="danger small-button" type="submit">Hapus</button></form></div></div></article>
<?php endforeach; ?><?php if (!$rows): ?><div class="card empty-panel">Belum ada media yang sesuai. Unggah berkas atau ubah pencarian.</div><?php endif; ?></div>
<?php if ($pages > 1): ?><nav class="pagination" aria-label="Halaman katalog media"><?php if ($page > 1): ?><a href="<?= e(base_url('panel/media/?' . http_build_query(['q' => $q, 'page' => $page - 1]))) ?>">← Sebelumnya</a><?php endif; ?><span><?= $page ?> / <?= $pages ?></span><?php if ($page < $pages): ?><a href="<?= e(base_url('panel/media/?' . http_build_query(['q' => $q, 'page' => $page + 1]))) ?>">Berikutnya →</a><?php endif; ?></nav><?php endif; ?>
</div>
<?php panel_end($user); ?>
