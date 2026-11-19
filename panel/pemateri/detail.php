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
$eventTotal=(int)panel_query('SELECT COUNT(*) FROM speaker_events WHERE speaker_id=:id',['id'=>$id])->fetchColumn();
$eventPages=max(1,(int)ceil($eventTotal/10));$eventPage=min($eventPages,positive_id($_GET['page']??'1')??1);
$events=panel_query('SELECT e.* FROM attendance_events e JOIN speaker_events se ON se.event_id=e.id WHERE se.speaker_id=:id ORDER BY e.tanggal DESC,e.id DESC LIMIT :limit OFFSET :offset',['id'=>$id,'limit'=>10,'offset'=>($eventPage-1)*10])->fetchAll();
panel_start($user,'Profil pemateri: '.$row['nama'],'pemateri');
?>
<div class="speaker-profile-workspace"><div class="page-actions"><h1><?= panel_icon('pemateri') ?> <?= e($row['nama']) ?></h1><a href="<?= e(base_url('panel/pemateri/')) ?>"><?= panel_icon('back') ?> Daftar pemateri</a><a class="button" href="<?= e(base_url('panel/pemateri/form.php?id='.$id)) ?>"><?= panel_icon('edit') ?> Edit profil</a></div>
<?php if($errors):?><div class="alert" role="alert"><?php foreach($errors as $error):?><p><?= e($error) ?></p><?php endforeach;?></div><?php endif;?>
<section class="card speaker-profile-card" aria-labelledby="speaker-profile-title">
<div class="speaker-profile-overview"><?php if($row['foto_file']):?><img class="speaker-profile-photo" src="<?= e(base_url('panel/pemateri/berkas.php?id='.$id.'&jenis=foto&v='.$row['version'])) ?>" alt="Foto <?= e($row['nama']) ?>" width="88" height="88"><?php else:?><span class="speaker-profile-photo speaker-profile-placeholder" aria-hidden="true"><?= panel_icon('pemateri') ?></span><?php endif; ?>
<div class="speaker-profile-information"><h2 id="speaker-profile-title">Profil pemateri</h2><dl class="speaker-profile-facts"><?php foreach(['instansi'=>['mitra','Instansi / perusahaan'],'keahlian'=>['program','Bidang keahlian'],'kelas_industri'=>['peserta','Kelas industri utama']] as $key=>[$icon,$label]):?><div><dt><?= panel_icon($icon) ?> <?= e($label) ?></dt><dd><?= e($row[$key]?:'Belum diisi') ?></dd></div><?php endforeach;?></dl></div></div>
<aside class="speaker-profile-file"><h2><?= panel_icon('capaian') ?> Curriculum vitae</h2><?php if($row['cv_file']):?><p class="speaker-cv-name" title="<?= e($row['cv_nama']) ?>"><?= e($row['cv_nama']) ?></p><small><?= number_format($row['cv_size']/1024,1) ?> KB</small><a class="button" href="<?= e(base_url('panel/pemateri/berkas.php?id='.$id.'&jenis=cv')) ?>"><?= panel_icon('download') ?> Unduh CV</a><?php else:?><p>CV belum diunggah.</p><a href="<?= e(base_url('panel/pemateri/form.php?id='.$id)) ?>"><?= panel_icon('upload') ?> Tambah CV</a><?php endif;?></aside>
<?php if(trim($row['bio'])!==''): ?><details class="speaker-profile-bio"><summary><?= panel_icon('identity') ?> Tentang pemateri</summary><p tabindex="0" aria-label="Biodata pemateri"><?= nl2br(e($row['bio'])) ?></p></details><?php endif; ?>
</section>
<section class="card speaker-profile-events"><div class="speaker-section-heading"><h2><?= panel_icon('calendar') ?> Kegiatan yang diampu</h2><span><?= $eventTotal ?> kegiatan</span></div><div class="table-scroll"><table><thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Angkatan</th><th>Kelas industri</th></tr></thead><tbody><?php foreach($events as $event):?><tr><td><a href="<?= e(base_url('panel/absensi/detail.php?id='.$event['id'])) ?>"><?= e($event['judul']) ?></a></td><td><?= e($event['tanggal']) ?></td><td><?= e((string)$event['angkatan']) ?></td><td><?= e($event['kelas_industri']) ?></td></tr><?php endforeach;?><?php if(!$events):?><tr><td colspan="4">Belum dikaitkan dengan kegiatan.</td></tr><?php endif;?></tbody></table></div><?php if($eventPages>1): ?><nav class="pagination" aria-label="Halaman kegiatan pemateri"><?php foreach(['Sebelumnya'=>$eventPage-1,'Berikutnya'=>$eventPage+1] as $label=>$target):if($target<1||$target>$eventPages){continue;} ?><a href="<?= e(base_url('panel/pemateri/detail.php?'.http_build_query(['id'=>$id,'page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach; ?><span>Halaman <?= $eventPage ?> dari <?= $eventPages ?></span></nav><?php endif; ?></section>
<form class="speaker-profile-delete" method="post" data-confirm="Hapus profil pemateri beserta CV dan fotonya? Kegiatan dan absensi tetap tersedia."><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="version" value="<?= $row['version'] ?>"><button class="danger" type="submit"><?= panel_icon('trash') ?> Hapus pemateri</button></form>
</div>
<?php panel_end($user); ?>
