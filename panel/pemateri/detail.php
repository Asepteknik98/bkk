<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/speakers.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
$row=panel_query('SELECT * FROM speakers WHERE id=:id',['id'=>$id])->fetch();if(!$row){panel_not_found();}$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    try{if(input_text($_POST,'aksi')!=='hapus'){throw new InvalidArgumentException('Tindakan tidak valid.');}$version=positive_id($_POST['version']??'');if(!$version){throw new InvalidArgumentException('Versi profil tidak valid.');}speaker_delete($id,$version);panel_message('Profil pemateri dan berkasnya dihapus.','panel/pemateri/');}
    catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
}
$events=panel_query('SELECT e.* FROM attendance_events e JOIN speaker_events se ON se.event_id=e.id WHERE se.speaker_id=:id ORDER BY e.tanggal DESC,e.id DESC',['id'=>$id])->fetchAll();
panel_start($user,'Profil pemateri: '.$row['nama'],'pemateri');
?>
<div class="page-actions"><h1><?= e($row['nama']) ?></h1><a href="<?= e(base_url('panel/pemateri/')) ?>">Daftar pemateri</a><a class="button" href="<?= e(base_url('panel/pemateri/form.php?id='.$id)) ?>">Edit profil / berkas</a></div>
<?php if($errors):?><div class="alert" role="alert"><?php foreach($errors as $error):?><p><?= e($error) ?></p><?php endforeach;?></div><?php endif;?>
<section class="card"><?php if($row['foto_file']):?><img src="<?= e(base_url('panel/pemateri/berkas.php?id='.$id.'&jenis=foto')) ?>" alt="Foto <?= e($row['nama']) ?>" width="160" height="160" style="object-fit:cover"><?php endif;?><h2>Profil pemateri</h2><dl><?php foreach(['instansi'=>'Instansi / perusahaan','keahlian'=>'Bidang keahlian','kelas_industri'=>'Kelas industri utama'] as $key=>$label):?><dt><?= e($label) ?></dt><dd><?= e($row[$key]?:'Belum diisi') ?></dd><?php endforeach;?></dl><p><?= nl2br(e($row['bio'])) ?></p><?php if($row['cv_file']):?><p><a class="button" href="<?= e(base_url('panel/pemateri/berkas.php?id='.$id.'&jenis=cv')) ?>">Download CV</a> <?= e($row['cv_nama']) ?> (<?= number_format($row['cv_size']/1024,1) ?> KB)</p><?php else:?><p>CV belum diunggah.</p><?php endif;?></section>
<section class="card"><h2>Kegiatan yang diampu</h2><div class="table-scroll"><table><thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Angkatan</th><th>Kelas industri</th></tr></thead><tbody><?php foreach($events as $event):?><tr><td><a href="<?= e(base_url('panel/absensi/detail.php?id='.$event['id'])) ?>"><?= e($event['judul']) ?></a></td><td><?= e($event['tanggal']) ?></td><td><?= e((string)$event['angkatan']) ?></td><td><?= e($event['kelas_industri']) ?></td></tr><?php endforeach;?><?php if(!$events):?><tr><td colspan="4">Belum dikaitkan dengan kegiatan.</td></tr><?php endif;?></tbody></table></div></section>
<form method="post" data-confirm="Hapus profil pemateri beserta CV dan fotonya? Kegiatan dan absensi tetap tersedia."><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="version" value="<?= $row['version'] ?>"><button class="danger" type="submit">Hapus pemateri</button></form>
<?php panel_end($user); ?>
