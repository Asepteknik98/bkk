<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
$user=require_role('admin','staf_bkk');$ready=pkl_ready();
$q=input_text($_GET,'q');$status=input_text($_GET,'status');
if(!valid_text($q,100,false)||!in_array($status,['','aktif','selesai','arsip'],true)){http_response_code(400);exit('Filter PKL tidak valid.');}
$where='1=1';$params=[];
if($q!==''){
    $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';
    $where.=" AND (s.nama LIKE :nama ESCAPE '!' OR s.nis LIKE :nis ESCAPE '!' OR p.tempat_pkl LIKE :tempat ESCAPE '!' OR p.guru_pembimbing LIKE :guru ESCAPE '!')";
    $params=['nama'=>$pattern,'nis'=>$pattern,'tempat'=>$pattern,'guru'=>$pattern];
}
if($status!==''){$where.=' AND p.status=:status';$params['status']=$status;}
$from=' FROM pkl_placements p JOIN siswa s ON s.id=p.siswa_id WHERE '.$where;
$total=$ready?(int)panel_query('SELECT COUNT(*)'.$from,$params)->fetchColumn():0;
$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=$ready?panel_query('SELECT p.*,s.nama,s.nis'.$from.' ORDER BY p.tanggal_mulai DESC,p.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll():[];
panel_start($user,'Praktik Kerja Lapangan','pkl');
?>
<div class="pkl-list-workspace">
<header class="page-actions"><div><h1><?= panel_icon('program') ?> Praktik Kerja Lapangan (PKL)</h1><p>Kelola penempatan, pembimbing, jurnal, dan laporan siswa.</p></div><nav class="pkl-list-toolbar" aria-label="Pengelolaan PKL"><a class="pkl-list-add" href="<?= e(base_url('panel/pkl/form.php')) ?>"><?= panel_icon('add') ?> Tambah penempatan</a><a href="<?= e(base_url('panel/pkl/jadwal.php')) ?>" title="Jadwal monitoring, pertemuan, dan sidang"><?= panel_icon('calendar') ?> Jadwal PKL</a><a href="<?= e(base_url('panel/peserta/')) ?>"><?= panel_icon('peserta') ?> Data siswa</a></nav></header>
<?php if(!$ready): ?><p class="notice">Modul belum aktif. Impor database/updates/019_pkl.sql. Riwayat PKL lama pada data peserta tetap tersedia.</p><?php endif; ?>
<form class="filter-form card" method="get"><label for="pkl-q"><span><?= panel_icon('search') ?> Cari siswa, tempat, atau guru</span><input id="pkl-q" type="search" name="q" maxlength="100" value="<?= e($q) ?>"></label><label for="pkl-status"><span><?= panel_icon('check-circle') ?> Status</span><select id="pkl-status" name="status"><?php foreach([''=>'Semua','aktif'=>'Aktif','selesai'=>'Selesai','arsip'=>'Arsip'] as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><button type="submit"><?= panel_icon('search') ?> Cari</button><a href="<?= e(base_url('panel/pkl/')) ?>">Reset</a></form>
<p><?= $total ?> penempatan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll pkl-list-sheet" data-vertical-list tabindex="0" role="region" aria-label="Daftar penempatan PKL, gulir untuk melihat data"><table><thead><tr><th>Siswa</th><th>Kelas / jurusan</th><th>Periode</th><th>Tempat PKL</th><th>Guru pembimbing</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach($rows as $row): ?><tr><td><strong><?= e($row['nama']) ?></strong></td><td><?= e($row['kelas']) ?><br><?= e($row['jurusan']) ?></td><td><?= e($row['tanggal_mulai']) ?> – <?= e($row['tanggal_selesai']) ?></td><td><?= e($row['tempat_pkl']) ?></td><td><?= e($row['guru_pembimbing']) ?></td><td><span class="pkl-status pkl-status-<?= e($row['status']) ?>"><?= e(ucfirst($row['status'])) ?></span></td><td><div class="pkl-list-actions"><a class="pkl-journal-action" href="<?= e(base_url('panel/pkl/detail.php?id='.$row['id'])) ?>" title="Jurnal dan laporan" aria-label="<?= e('Jurnal dan laporan '.$row['nama']) ?>"><?= panel_icon('lamaran') ?></a><a class="pkl-edit-action" href="<?= e(base_url('panel/pkl/form.php?id='.$row['id'])) ?>" title="Edit penempatan" aria-label="<?= e('Edit penempatan '.$row['nama']) ?>"><?= panel_icon('edit') ?></a></div></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="7">Belum ada penempatan yang sesuai.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman PKL"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/pkl/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
</div>
<?php panel_end($user); ?>
