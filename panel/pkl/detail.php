<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
$user=require_role('admin','staf_bkk');pkl_require_ready();
$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$placement=pkl_find($id);}catch(InvalidArgumentException){panel_not_found();}
$journalId=positive_id($_GET['jurnal']??'');if(isset($_GET['jurnal'])&&!$journalId){panel_not_found();}
$editing=$journalId?panel_query('SELECT * FROM pkl_journals WHERE id=:id AND placement_id=:placement',['id'=>$journalId,'placement'=>$id])->fetch():null;
if($journalId&&!$editing){panel_not_found();}
$today=date('Y-m-d');$data=$editing?:['tanggal'=>max($placement['tanggal_mulai'],min($today,$placement['tanggal_selesai'])),'kegiatan'=>'','catatan'=>''];$version=(int)($editing['version']??0);$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$action=input_text($_POST,'aksi');
    $raw=input_text($_POST,'jurnal_id');$journalId=$raw===''?null:positive_id($raw);
    $version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    foreach(['tanggal','kegiatan','catatan'] as $key){$data[$key]=input_text($_POST,$key);}
    try{
        if(!in_array($action,['simpan','hapus'],true)||($raw!==''&&!$journalId)){throw new InvalidArgumentException('Tindakan atau jurnal tidak valid.');}
        pkl_journal_change($id,$journalId,$version,array_intersect_key($data,array_flip(['tanggal','kegiatan','catatan'])),(int)$user['id'],$action==='hapus');
        panel_message($action==='hapus'?'Jurnal dihapus.':'Jurnal harian berhasil disimpan.','panel/pkl/detail.php?id='.$id);
    }catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
$journals=panel_query('SELECT * FROM pkl_journals WHERE placement_id=:id ORDER BY tanggal,id',['id'=>$id])->fetchAll();$weeks=pkl_weekly($journals);
$pages=max(1,(int)ceil(count($journals)/20));$page=min($pages,positive_id($_GET['page']??'1')??1);$visible=array_slice($journals,($page-1)*20,20);
$report=panel_query('SELECT updated_at FROM pkl_final_reports WHERE placement_id=:id',['id'=>$id])->fetch();
panel_start($user,'Detail PKL — '.$placement['nama'],'pkl');
?>
<div class="page-actions"><h1>PKL: <?= e($placement['nama']) ?></h1><a href="<?= e(base_url('panel/pkl/')) ?>">Daftar PKL</a><a href="<?= e(base_url('panel/pkl/form.php?id='.$id)) ?>">Edit penempatan</a><a href="<?= e(base_url('panel/pkl/laporan.php?id='.$id)) ?>">Laporan akhir</a><a href="<?= e(base_url('panel/pkl/unduh.php?id='.$id.'&jenis=rekap')) ?>">Unduh rekap Excel</a></div>
<section class="card"><h2>Penempatan dan pembimbing</h2><dl class="response-details"><dt>NIS</dt><dd><?= e($placement['nis']) ?></dd><?php foreach(pkl_fields() as $key=>[$label,$max]): ?><dt><?= e($label) ?></dt><dd><?= nl2br(e($placement[$key])) ?></dd><?php endforeach; ?></dl><p>Guru pembimbing merupakan data penugasan. <?= count($journals) ?> hari memiliki jurnal; angka ini bukan rekap kehadiran.</p><?php if($report): ?><p>Laporan akhir tersimpan, diperbarui <?= e($report['updated_at']) ?>. Jurnal yang ditampilkan pada unduhan mengikuti data terbaru.</p><?php endif; ?></section>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<details class="bmw-disclosure" <?= $journalId||$errors?'open':'' ?>><summary><?= $journalId?'Edit jurnal harian':'Tambah jurnal harian' ?></summary>
<form class="editor-form card" method="post" action="<?= e(base_url('panel/pkl/detail.php?id='.$id)) ?>"><?= csrf_field() ?><input type="hidden" name="aksi" value="simpan"><input type="hidden" name="jurnal_id" value="<?= $journalId??'' ?>"><input type="hidden" name="version" value="<?= $version ?>">
<p>Satu jurnal per tanggal. Tulis beberapa kegiatan pada kolom yang sama jika diperlukan.</p>
<label for="tanggal">Tanggal<input id="tanggal" name="tanggal" type="date" min="<?= e($placement['tanggal_mulai']) ?>" max="<?= e($placement['tanggal_selesai']) ?>" value="<?= e($data['tanggal']) ?>" required></label>
<label for="kegiatan">Kegiatan<textarea id="kegiatan" name="kegiatan" maxlength="10000" rows="4" required><?= e($data['kegiatan']) ?></textarea></label>
<label for="catatan">Catatan (opsional)<textarea id="catatan" name="catatan" maxlength="5000" rows="3"><?= e($data['catatan']) ?></textarea></label><button>Simpan jurnal</button><?php if($journalId): ?><a href="<?= e(base_url('panel/pkl/detail.php?id='.$id)) ?>">Batal edit</a><?php endif; ?></form></details>
<h2>Jurnal harian</h2><div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Catatan</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach($visible as $row): ?><tr><td><?= e($row['tanggal']) ?></td><td><?= nl2br(e($row['kegiatan'])) ?></td><td><?= nl2br(e($row['catatan'])) ?></td><td><a href="<?= e(base_url('panel/pkl/detail.php?id='.$id.'&jurnal='.$row['id'])) ?>">Edit</a><form method="post" action="<?= e(base_url('panel/pkl/detail.php?id='.$id)) ?>" data-confirm="Hapus jurnal tanggal ini?"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="jurnal_id" value="<?= $row['id'] ?>"><input type="hidden" name="version" value="<?= $row['version'] ?>"><button class="danger small-button">Hapus</button></form></td></tr><?php endforeach; ?><?php if(!$visible): ?><tr><td colspan="4">Belum ada jurnal harian.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman jurnal"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/pkl/detail.php?id='.$id.'&page='.$target)) ?>"><?= $label ?></a><?php endforeach; ?></nav>
<h2>Rekap kegiatan mingguan</h2><p>Senin–Minggu, berdasarkan tanggal jurnal. Minggu tanpa jurnal tidak ditampilkan.</p>
<div class="table-scroll"><table><thead><tr><th>Minggu</th><th>Rentang kalender</th><th>Hari tercatat</th><th>Kegiatan</th><th>Catatan</th></tr></thead><tbody><?php foreach($weeks as $week): ?><tr><td><?= e($week['minggu']) ?></td><td><?= e($week['mulai'].' – '.$week['selesai']) ?></td><td><?= $week['hari'] ?></td><td><?= nl2br(e(implode("\n",$week['kegiatan']))) ?></td><td><?= nl2br(e(implode("\n",$week['catatan']))) ?></td></tr><?php endforeach; ?><?php if(!$weeks): ?><tr><td colspan="5">Rekap akan terisi setelah jurnal disimpan.</td></tr><?php endif; ?></tbody></table></div>
<?php panel_end($user); ?>
