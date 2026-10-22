<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin','staf_bkk');
$items=panel_query('SELECT * FROM mitra ORDER BY nama')->fetchAll();
panel_start($user,'Mitra','mitra');
?>
<div class="partners-admin"><div class="page-actions"><h1><?= panel_icon('mitra') ?> Mitra <span class="badge"><?= count($items) ?> mitra</span></h1><a class="button" href="<?= e(base_url('panel/mitra/tambah.php')) ?>">Tambah mitra</a></div><p class="partners-admin-note">Hanya mitra aktif yang ditampilkan pada beranda publik.</p>
<div class="table-scroll"><table><thead><tr><th>Nama</th><th>Jenis</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($items as $item): ?><tr><td><div class="partner-admin-identity"><?php if($item['logo'] && managed_upload_path($item['logo'])): ?><img src="<?= e(base_url($item['logo'])) ?>" alt="" width="32" height="32"><?php else: ?><span class="partner-admin-placeholder"><?= panel_icon('mitra') ?></span><?php endif; ?><strong><?= e($item['nama']) ?></strong></div></td><td><span class="partner-kind"><?= panel_icon($item['jenis']==='kampus'?'program':'mitra') ?><?= $item['jenis']==='kampus'?'Perguruan tinggi':'Perusahaan' ?></span></td><td><span class="user-status <?= $item['aktif']?'is-active':'is-inactive' ?>"><?= $item['aktif']?'Aktif':'Nonaktif' ?></span></td><td><div class="row-actions"><a class="button secondary" href="<?= e(base_url('panel/mitra/edit.php?id='.$item['id'])) ?>">Edit</a><form method="post" action="<?= e(base_url('panel/mitra/hapus.php')) ?>" data-confirm="Hapus mitra ini? Berkas logo tetap berada di katalog."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $item['id'] ?>"><button class="partner-delete" type="submit">Hapus</button></form></div></td></tr><?php endforeach; ?>
<?php if(!$items): ?><tr><td colspan="4">Belum ada mitra.</td></tr><?php endif; ?></tbody></table></div>
</div>
<?php panel_end($user); ?>
