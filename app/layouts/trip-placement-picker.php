<?php
if (!isset($user)) { http_response_code(403); exit; }
$query = input_text($_GET, 'q');
if (!valid_text($query, 100, false)) { http_response_code(400); exit('Pencarian maksimal 100 karakter.'); }
$allPlacements = (int)panel_query('SELECT COUNT(*) FROM pkl_placements')->fetchColumn();
$pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query) . '%';
$pickerParams = ['nama'=>$pattern, 'tempat'=>$pattern];
$pickerFrom = " FROM pkl_placements p JOIN siswa s ON s.id=p.siswa_id WHERE s.nama LIKE :nama ESCAPE '!' OR p.tempat_pkl LIKE :tempat ESCAPE '!'";
$found = (int)panel_query('SELECT COUNT(*)' . $pickerFrom, $pickerParams)->fetchColumn();
$pickerPages = max(1, (int)ceil($found / 10));
$pickerPage = min($pickerPages, positive_id($_GET['page'] ?? '1') ?? 1);
$placements = panel_query('SELECT p.id,p.tanggal_mulai,p.tanggal_selesai,p.tempat_pkl,s.nama' . $pickerFrom . ' ORDER BY p.tanggal_mulai DESC,p.id DESC LIMIT :limit OFFSET :offset', $pickerParams + ['limit'=>10,'offset'=>($pickerPage-1)*10])->fetchAll();
$createPlacementUrl = base_url('panel/pkl/form.php?lanjut=perjalanan');
?>
<div class="trip-picker-intro"><div><h2><?= panel_icon('peserta') ?> Pilih siswa dan tempat PKL</h2><p>Catatan perjalanan harus terkait penempatan PKL, yaitu siswa, tempat PKL, dan periode pelaksanaannya. Data siswa saja belum cukup.</p></div><a class="trip-create-placement" href="<?= e($createPlacementUrl) ?>"><?= panel_icon('add') ?> Buat penempatan PKL</a></div>
<?php if (!$allPlacements): ?>
<section class="trip-picker-empty"><h2>Belum ada penempatan PKL</h2><p>Anda belum bisa mencatat perjalanan. Buat penempatan siswa terlebih dahulu dengan tempat, periode, dan guru pembimbing.</p><ol><li>Klik <strong>Buat penempatan PKL</strong>, lalu pilih siswa. Jika siswa belum terdaftar, pilih <strong>Tambah siswa baru</strong>.</li><li>Lengkapi dan simpan penempatannya.</li><li>Anda langsung kembali ke formulir perjalanan untuk mengisi tanggal, petugas, dan biaya.</li></ol><a class="trip-create-placement" href="<?= e($createPlacementUrl) ?>"><?= panel_icon('add') ?> Mulai buat penempatan</a></section>
<?php else: ?>
<form class="filter-form card" method="get"><label for="trip-placement-search">Cari nama siswa atau tempat PKL<input id="trip-placement-search" name="q" type="search" maxlength="100" value="<?= e($query) ?>" placeholder="Nama siswa atau perusahaan tempat PKL"></label><button type="submit"><?= panel_icon('search') ?> Cari</button><?php if ($query !== ''): ?><a href="<?= e(base_url('panel/biaya-perjalanan/form.php')) ?>">Reset pencarian</a><?php endif; ?></form>
<div class="trip-list-heading"><h2>Penempatan yang tersedia</h2><span><?= $found ?> hasil &middot; Halaman <?= $pickerPage ?> dari <?= $pickerPages ?></span></div>
<div class="table-scroll" data-vertical-list tabindex="0" role="region" aria-label="Pilih penempatan untuk mencatat perjalanan"><table><thead><tr><th>Siswa</th><th>Tempat PKL</th><th>Periode PKL</th><th>Tindakan</th></tr></thead><tbody><?php foreach ($placements as $item): ?><tr><td><strong><?= e($item['nama']) ?></strong></td><td><?= e($item['tempat_pkl']) ?></td><td><?= e($item['tanggal_mulai'].' - '.$item['tanggal_selesai']) ?></td><td><a class="trip-use-placement" href="<?= e(base_url('panel/biaya-perjalanan/form.php?placement_id='.$item['id'])) ?>"><?= panel_icon('next') ?> Gunakan penempatan ini</a></td></tr><?php endforeach; ?><?php if (!$placements): ?><tr><td colspan="4" class="empty-panel">Tidak ada penempatan yang sesuai pencarian. Reset pencarian atau buat penempatan jika siswa belum memiliki penempatan PKL.</td></tr><?php endif; ?></tbody></table></div>
<?php if ($pickerPages > 1): $start=max(1,min($pickerPage-4,$pickerPages-9)); $end=min($pickerPages,$start+9); ?><nav class="pagination" aria-label="Halaman pilihan penempatan"><?php if ($pickerPage > 1): ?><a href="<?= e(base_url('panel/biaya-perjalanan/form.php?'.http_build_query(['q'=>$query,'page'=>$pickerPage-1]))) ?>">Sebelumnya</a><?php endif; ?><?php for ($n=$start;$n<=$end;$n++): ?><a href="<?= e(base_url('panel/biaya-perjalanan/form.php?'.http_build_query(['q'=>$query,'page'=>$n]))) ?>" <?= $n===$pickerPage?'aria-current="page"':'' ?>><?= $n ?></a><?php endfor; ?><?php if ($pickerPage < $pickerPages): ?><a href="<?= e(base_url('panel/biaya-perjalanan/form.php?'.http_build_query(['q'=>$query,'page'=>$pickerPage+1]))) ?>">Berikutnya</a><?php endif; ?></nav><?php endif; ?>
<?php endif; ?>
