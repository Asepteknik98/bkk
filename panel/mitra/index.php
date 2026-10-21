<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin','staf_bkk');
$items=panel_query('SELECT * FROM mitra ORDER BY nama')->fetchAll();
panel_start($user,'Mitra','mitra');
?>
<div class="page-actions"><h1>Mitra</h1><a class="button" href="<?= e(base_url('panel/mitra/tambah.php')) ?>">Tambah mitra</a></div><p>Hanya mitra aktif yang ditampilkan pada beranda publik.</p>
<div class="table-scroll"><table><thead><tr><th>Nama</th><th>Jenis</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($items as $item): ?><tr><td><?= e($item['nama']) ?></td><td><?= $item['jenis']==='kampus'?'Perguruan tinggi':'Perusahaan' ?></td><td><?= $item['aktif']?'Aktif':'Nonaktif' ?></td><td><a href="<?= e(base_url('panel/mitra/edit.php?id='.$item['id'])) ?>">Edit</a><form method="post" action="<?= e(base_url('panel/mitra/hapus.php')) ?>" data-confirm="Hapus mitra ini? Berkas logo tetap berada di katalog."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $item['id'] ?>"><button type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
<?php if(!$items): ?><tr><td colspan="4">Belum ada mitra.</td></tr><?php endif; ?></tbody></table></div>
<?php panel_end($user); ?>
