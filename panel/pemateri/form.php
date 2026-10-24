<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/speakers.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');$old=null;$errors=[];$selected=[];$data=speaker_input([]);$version='';
if($id){$old=panel_query('SELECT * FROM speakers WHERE id=:id',['id'=>$id])->fetch();if(!$old){panel_not_found();}$data=speaker_input($old);$version=(string)$old['version'];$selected=array_map('intval',panel_query('SELECT event_id FROM speaker_events WHERE speaker_id=:id',['id'=>$id])->fetchAll(PDO::FETCH_COLUMN));}
else{$industry=input_text($_GET,'kelas_industri');if(valid_text($industry,150,false)){$data['kelas_industri']=$industry;}$eventId=positive_id($_GET['event_id']??'');if($eventId){$selected=[$eventId];}}
$remove=['cv'=>false,'foto'=>false];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=speaker_input($_POST);$errors=speaker_errors($data);$version=input_text($_POST,'version');$uploads=[];$saved=false;
    $remove=['cv'=>($_POST['hapus_cv']??'')==='1','foto'=>($_POST['hapus_foto']??'')==='1'];
    try{
        $selected=speaker_event_ids($_POST['events']??[]);
        if($id&&!positive_id($version)){throw new InvalidArgumentException('Versi profil tidak valid. Muat ulang halaman.');}
        if(!$errors){
            foreach(['cv','foto'] as $kind){if(isset($_FILES[$kind])&&!is_array($_FILES[$kind])){throw new InvalidArgumentException('Unggahan tidak valid.');}$upload=speaker_upload($_FILES[$kind]??null,$kind);if($upload){$uploads[$kind]=$upload;}}
            $savedId=speaker_save($data,$id,$id?(int)$version:null,(int)$user['id'],$uploads,$remove,$selected);$saved=true;
            panel_message('Profil pemateri berhasil disimpan.','panel/pemateri/detail.php?id='.$savedId);
        }
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
    catch(Throwable $exception){error_log('Penyimpanan pemateri gagal: '.get_class($exception));$errors[]='Profil gagal disimpan. Silakan coba kembali.';}
    finally{if(!$saved){foreach($uploads as $upload){speaker_remove_file($upload['file']);}}}
}
$events=panel_query('SELECT id,judul,tanggal,angkatan,kelas_industri FROM attendance_events ORDER BY tanggal DESC,id DESC')->fetchAll();
$industries=panel_query("SELECT kelas_industri FROM speakers WHERE kelas_industri<>'' UNION SELECT kelas_industri FROM siswa WHERE kelas_industri<>'' UNION SELECT kelas_industri FROM attendance_events ORDER BY kelas_industri")->fetchAll(PDO::FETCH_COLUMN);
panel_start($user,$id?'Edit pemateri':'Tambah pemateri','pemateri');
?>
<div class="speaker-form-workspace">
<div class="page-actions"><h1><?= $id?'Edit pemateri':'Tambah pemateri' ?></h1><a href="<?= e(base_url($id?'panel/pemateri/detail.php?id='.$id:'panel/pemateri/')) ?>"><?= panel_icon('back') ?> Kembali</a></div>
<?php if($errors):?><div class="alert" role="alert"><strong>Profil belum disimpan.</strong><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><p>Pilih kembali berkas unggahan jika diperlukan.</p><a href="<?= e(base_url('panel/pemateri/form.php'.($id?'?id='.$id:''))) ?>">Muat ulang data terbaru</a></div><?php endif;?>
<form class="editor-form card" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="version" value="<?= e($version) ?>">
<?php foreach(['nama'=>['Nama pemateri',150],'instansi'=>['Instansi / perusahaan',190],'keahlian'=>['Bidang keahlian',190],'kelas_industri'=>['Kelas industri utama (opsional)',150]] as $key=>[$label,$max]):?><label for="<?= $key ?>"><span><?= panel_icon(['nama'=>'pemateri','instansi'=>'mitra','keahlian'=>'program','kelas_industri'=>'master'][$key]) ?> <?= e($label) ?></span><input id="<?= $key ?>" name="<?= $key ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= $key==='nama'?'required':'' ?> <?= $key==='kelas_industri'?'list="speaker-industries"':'' ?>></label><?php endforeach;?>
<datalist id="speaker-industries"><?php foreach($industries as $industry):?><option value="<?= e($industry) ?>"><?php endforeach;?></datalist>
<label for="bio"><span><?= panel_icon('message') ?> Profil singkat (opsional)</span><textarea id="bio" name="bio" rows="2" maxlength="5000"><?= e($data['bio']) ?></textarea></label>
<label for="cv"><span><?= panel_icon('upload') ?> CV pemateri — PDF maksimal 5 MB (opsional)</span><input id="cv" name="cv" type="file" accept=".pdf,application/pdf"></label>
<?php if($old&&$old['cv_file']):?><p>CV saat ini: <a href="<?= e(base_url('panel/pemateri/berkas.php?id='.$id.'&jenis=cv')) ?>"><?= e($old['cv_nama']) ?></a></p><label class="check-label"><input type="checkbox" name="hapus_cv" value="1" <?= $remove['cv']?'checked':'' ?>>Hapus CV saat ini</label><?php endif;?>
<label for="foto"><span><?= panel_icon('media') ?> Foto pemateri — JPG, PNG, WebP maksimal 3 MB (opsional)</span><input id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
<?php if($old&&$old['foto_file']):?><img src="<?= e(base_url('panel/pemateri/berkas.php?id='.$id.'&jenis=foto')) ?>" alt="Foto pemateri saat ini" width="128" height="128" style="object-fit:cover"><label class="check-label"><input type="checkbox" name="hapus_foto" value="1" <?= $remove['foto']?'checked':'' ?>>Hapus foto saat ini</label><?php endif;?>
<p>Kosongkan unggahan untuk mempertahankan berkas lama. Unggahan baru menggantikan berkas lama, termasuk jika opsi hapus dicentang.</p>
<fieldset><legend><?= panel_icon('calendar') ?> Kegiatan yang diampu (opsional)</legend><p>Boleh memilih beberapa kegiatan, maksimal 100. Satu kegiatan dapat memiliki beberapa pemateri.</p><div style="max-height:20rem;overflow:auto"><?php foreach($events as $event):?><label class="check-label"><input type="checkbox" name="events[]" value="<?= $event['id'] ?>" <?= in_array((int)$event['id'],$selected,true)?'checked':'' ?>><?= e($event['tanggal'].' — '.$event['judul'].' · '.$event['angkatan'].' · '.$event['kelas_industri']) ?></label><?php endforeach;?><?php if(!$events):?><p>Belum ada kegiatan. Buat melalui menu Absensi Kegiatan, lalu kaitkan di sini.</p><?php endif;?></div></fieldset>
<div class="form-actions"><button type="submit"><?= panel_icon('save') ?> Simpan pemateri</button><a href="<?= e(base_url('panel/pemateri/')) ?>">Batal</a></div></form>
</div>
<?php panel_end($user); ?>
