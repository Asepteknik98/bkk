<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-classes.php';
$user=require_role('admin','staf_bkk');industry_require_ready();$classId=positive_id($_GET['class_id']??'');$id=positive_id($_GET['id']??'');if(!$classId||!$id){panel_not_found();}
try{$class=industry_find($classId);}catch(InvalidArgumentException){panel_not_found();}
$old=panel_query('SELECT * FROM industry_class_meetings WHERE id=:id AND class_id=:class',['id'=>$id,'class'=>$classId])->fetch();if(!$old){panel_not_found();}
$data=industry_meeting_input(array_map(static fn($value)=>(string)($value??''),$old));$data['jam']=substr($data['jam'],0,5);$version=(int)$old['version'];$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=industry_meeting_input($_POST);$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{industry_meeting_save($classId,$id,$version,$data,(int)$user['id']);panel_message('Pertemuan berhasil disimpan.','panel/kelas-industri/detail.php?id='.$classId);}
    catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
$speakers=panel_query('SELECT id,nama,instansi FROM speakers ORDER BY nama,id')->fetchAll();
panel_start($user,'Edit pertemuan Kelas Industri','kelas-industri');
?>
<div class="page-actions"><h1>Pertemuan <?= (int)$old['urutan'] ?>: <?= e($class['nama']) ?></h1><a href="<?= e(base_url('panel/kelas-industri/detail.php?id='.$classId)) ?>">Kembali ke kelas</a></div>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<label for="tanggal">Tanggal<input id="tanggal" name="tanggal" type="date" value="<?= e($data['tanggal']) ?>"></label><label for="jam">Jam (WIB)<input id="jam" name="jam" type="time" value="<?= e($data['jam']) ?>"></label>
<label for="lokasi">Lokasi<input id="lokasi" name="lokasi" maxlength="200" value="<?= e($data['lokasi']) ?>"></label>
<label for="pemateri_id">Pemateri pertemuan<select id="pemateri_id" name="pemateri_id"><option value="">Gunakan pemateri kelas</option><?php foreach($speakers as $speaker): ?><option value="<?= $speaker['id'] ?>" <?= (string)$speaker['id']===$data['pemateri_id']?'selected':'' ?>><?= e($speaker['nama'].($speaker['instansi']?' — '.$speaker['instansi']:'')) ?></option><?php endforeach; ?></select></label>
<label for="materi">Materi / kegiatan (opsional)<textarea id="materi" name="materi" maxlength="10000" rows="4"><?= e($data['materi']) ?></textarea></label>
<label for="status">Status pertemuan<select id="status" name="status"><?php foreach(industry_meeting_statuses() as $key=>$label): ?><option value="<?= $key ?>" <?= $data['status']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<p>Tanggal, jam, dan lokasi wajib untuk status Dijadwalkan, Terlaksana, atau Ditunda. Pertemuan mendatang belum dapat ditandai Terlaksana.</p>
<label for="catatan">Catatan pelaksanaan / penundaan (opsional)<textarea id="catatan" name="catatan" maxlength="10000" rows="4"><?= e($data['catatan']) ?></textarea></label><button>Simpan pertemuan</button></form>
<?php panel_end($user); ?>
