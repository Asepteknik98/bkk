<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/attendance.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
$event=panel_query('SELECT * FROM attendance_events WHERE id=:id',['id'=>$id])->fetch();if(!$event){panel_not_found();}
$total=(int)panel_query('SELECT COUNT(*) FROM attendance_records WHERE event_id=:id',['id'=>$id])->fetchColumn();
$pages=max(1,(int)ceil($total/25));$page=min($pages,positive_id($_GET['page']??'1')??1);$errors=[];$submitted=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    try{
        $version=positive_id($_POST['version']??'');if(!$version){throw new InvalidArgumentException('Versi data tidak valid. Muat ulang halaman.');}
        $action=input_text($_POST,'aksi');
        if($action==='kegiatan'){attendance_edit($id,$version,$_POST);}
        elseif($action==='absensi'){
            if(!isset($_POST['records'])||!is_array($_POST['records'])){throw new InvalidArgumentException('Daftar absensi tidak valid.');}
            $submitted=$_POST['records'];attendance_save($id,$version,$page,$submitted,(int)$user['id']);
        }else{throw new InvalidArgumentException('Tindakan tidak valid.');}
        panel_message('Perubahan berhasil disimpan.','panel/absensi/detail.php?'.http_build_query(['id'=>$id,'page'=>$page]));
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
}
// Keep the submitted version on conflict so retries cannot overwrite newer data.
$formVersion=$errors?($_POST['version']??''):(string)$event['version'];if(!is_string($formVersion)){$formVersion='';}
$rows=panel_query('SELECT * FROM attendance_records WHERE event_id=:id ORDER BY nama,id LIMIT :limit OFFSET :offset',['id'=>$id,'limit'=>25,'offset'=>($page-1)*25])->fetchAll();
$speakersReady=(bool)panel_query("SHOW TABLES LIKE 'speakers'")->fetchColumn();
$speakers=$speakersReady?panel_query('SELECT s.id,s.nama,s.instansi FROM speakers s JOIN speaker_events se ON se.speaker_id=s.id WHERE se.event_id=:id ORDER BY s.nama,s.id',['id'=>$id])->fetchAll():[];
$counts=array_fill_keys(array_keys(attendance_statuses()),0);$pending=0;
foreach(panel_query('SELECT status,COUNT(*) AS jumlah FROM attendance_records WHERE event_id=:id GROUP BY status',['id'=>$id])->fetchAll() as $count){if($count['status']===null){$pending=(int)$count['jumlah'];}else{$counts[$count['status']]=(int)$count['jumlah'];}}
panel_start($user,'Absensi: '.$event['judul'],'absensi');
?>
<div class="page-actions"><a class="button" href="<?= e(base_url('panel/ekspor.php?'.http_build_query(['jenis'=>'absensi','id'=>$id]))) ?>">Download Excel absensi kegiatan ini</a></div>
<div class="page-actions"><h1><?= e($event['judul']) ?></h1><a href="<?= e(base_url('panel/absensi/')) ?>">Daftar kegiatan</a><a href="<?= e(base_url('panel/absensi/rekap.php?'.http_build_query(['angkatan'=>$event['angkatan'],'kelas_industri'=>$event['kelas_industri']]))) ?>">Rekap siswa</a></div>
<p><?= e($event['tanggal']) ?> · Angkatan <?= e((string)$event['angkatan']) ?> · <?= e($event['kelas_industri']) ?></p>
<?php if($speakersReady):?><section class="card"><h2>Pemateri kegiatan</h2><?php if($speakers):?><ul><?php foreach($speakers as $speaker):?><li><a href="<?= e(base_url('panel/pemateri/detail.php?id='.$speaker['id'])) ?>"><?= e($speaker['nama']) ?></a><?= $speaker['instansi']!==''?' — '.e($speaker['instansi']):'' ?></li><?php endforeach;?></ul><?php else:?><p>Belum ada pemateri yang dikaitkan.</p><?php endif;?><div class="row-actions"><a href="<?= e(base_url('panel/pemateri/form.php?'.http_build_query(['event_id'=>$id,'kelas_industri'=>$event['kelas_industri']]))) ?>">Tambah pemateri baru</a><a href="<?= e(base_url('panel/pemateri/')) ?>">Pilih pemateri yang sudah ada melalui Edit</a></div></section><?php endif;?>
<?php if($errors):?><div class="alert" role="alert"><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><a href="<?= e(base_url('panel/absensi/detail.php?'.http_build_query(['id'=>$id,'page'=>$page]))) ?>">Muat ulang data terbaru</a></div><?php endif;?>
<details class="card"><summary>Edit informasi kegiatan</summary><form class="editor-form" method="post"><?= csrf_field() ?><input type="hidden" name="aksi" value="kegiatan"><input type="hidden" name="version" value="<?= e($formVersion) ?>"><label>Nama kegiatan<input name="judul" maxlength="190" required value="<?= e(input_text($errors&&($_POST['aksi']??'')==='kegiatan'?$_POST:$event,'judul')) ?>"></label><label>Tanggal<input type="date" name="tanggal" min="1900-01-01" max="2199-12-31" required value="<?= e(input_text($errors&&($_POST['aksi']??'')==='kegiatan'?$_POST:$event,'tanggal')) ?>"></label><label>Keterangan<textarea name="deskripsi" rows="3" maxlength="5000"><?= e(input_text($errors&&($_POST['aksi']??'')==='kegiatan'?$_POST:$event,'deskripsi')) ?></textarea></label><p>Angkatan, kelas industri, dan daftar peserta mengikuti data saat kegiatan dibuat.</p><button type="submit">Simpan informasi kegiatan</button></form></details>
<p><?php foreach(attendance_statuses() as $key=>$label):?><?= e($label) ?>: <strong><?= $counts[$key] ?></strong> · <?php endforeach;?>Belum diisi: <strong><?= $pending ?></strong></p>
<form class="card" method="post"><?= csrf_field() ?><input type="hidden" name="aksi" value="absensi"><input type="hidden" name="version" value="<?= e($formVersion) ?>"><h2>Absensi siswa</h2><p>Status kosong berarti belum diisi. Simpan setiap halaman sebelum pindah halaman.</p>
<div class="table-scroll"><table><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Status</th><th>Keterangan</th></tr></thead><tbody><?php foreach($rows as $row):$values=$row;if(isset($submitted[$row['id']])&&is_array($submitted[$row['id']])){$values=array_replace($row,array_intersect_key($submitted[$row['id']],array_flip(['status','keterangan'])));} $status=is_string($values['status'])?$values['status']:'';$note=is_string($values['keterangan'])?$values['keterangan']:'';?><tr><td><?= e($row['nis']) ?></td><td><?= e($row['nama']) ?></td><td><?= e($row['kelas']) ?></td><td><select name="records[<?= $row['id'] ?>][status]" aria-label="Status <?= e($row['nama']) ?>"><option value="">Belum diisi</option><?php foreach(attendance_statuses() as $key=>$label):?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select></td><td><input name="records[<?= $row['id'] ?>][keterangan]" maxlength="500" value="<?= e($note) ?>" aria-label="Keterangan <?= e($row['nama']) ?>"></td></tr><?php endforeach;?></tbody></table></div>
<button type="submit">Simpan absensi halaman ini</button></form>
<p><?= $total ?> siswa · Halaman <?= $page ?> dari <?= $pages ?></p>
<nav class="pagination" aria-label="Halaman absensi"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/absensi/detail.php?'.http_build_query(['id'=>$id,'page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav>
<?php panel_end($user); ?>
