<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl-schedules.php';
$user=require_role('admin','staf_bkk');pkl_schedules_require_ready();
$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=null;if($id){try{$old=pkl_schedule_find($id);}catch(InvalidArgumentException){panel_not_found();}}
$kind=$old['jenis']??input_text($_GET,'jenis');if(!isset(pkl_schedule_types()[$kind])){http_response_code(400);exit('Pilih jenis jadwal yang tersedia.');}
$placementId=$old?(int)$old['placement_id']:(positive_id($_GET['placement_id']??'')??0);
$placement=null;if($placementId){try{$placement=pkl_find($placementId);}catch(InvalidArgumentException){panel_not_found();}}
$data=$old?pkl_schedule_input($old):pkl_schedule_input(['jenis'=>$kind,'status'=>'dijadwalkan']);$version=(int)($old['version']??0);$errors=[];
if(!$old&&$placement){$data=array_replace($data,['tanggal'=>$placement['tanggal_mulai'],'jam'=>'08:00','petugas'=>$placement['guru_pembimbing'],'tujuan'=>$placement['tempat_pkl'],'alamat_lengkap'=>$placement['alamat_lengkap'],'agenda'=>pkl_schedule_types()[$kind]]);}
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();if(!$placement){http_response_code(400);exit('Pilih penempatan terlebih dahulu.');}
    $data=pkl_schedule_input($_POST);$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{
        if($data['jenis']!==$kind){throw new InvalidArgumentException('Jenis jadwal tidak sesuai halaman.');}
        pkl_schedule_save($placementId,$id,$version,$data,(int)$user['id']);panel_message('Jadwal PKL berhasil disimpan.','panel/pkl/jadwal.php?placement_id='.$placementId);
    }catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
panel_start($user,($id?'Edit ':'Tambah ').pkl_schedule_types()[$kind],'pkl');
?>
<div class="page-actions"><h1><?= $id?'Edit':'Tambah' ?> <?= e(pkl_schedule_types()[$kind]) ?></h1><a href="<?= e(base_url('panel/pkl/jadwal.php')) ?>">Daftar jadwal</a></div>
<?php if(!$placement):$pickerAction='panel/pkl/jadwal-form.php';$pickerParams=['jenis'=>$kind];require APP_ROOT.'/app/layouts/pkl-placement-picker.php';else: ?>
<p><strong><?= e($placement['nama']) ?></strong> · NIS <?= e($placement['nis']) ?> · Periode <?= e($placement['tanggal_mulai'].' – '.$placement['tanggal_selesai']) ?></p>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="jenis" value="<?= e($kind) ?>"><input type="hidden" name="version" value="<?= $version ?>">
<p>Guru dicatat sebagai penugasan. Untuk beberapa pembimbing/penguji, tulis nama-namanya pada kolom petugas. Gunakan status Dibatalkan untuk mempertahankan riwayat jadwal.</p>
<?php foreach(pkl_schedule_fields() as $key=>[$label,$max]):
if($key==='orang_tua_wali'&&$kind!=='orang_tua'){continue;}if(in_array($key,['hasil_monitoring','tindak_lanjut'],true)&&$kind!=='monitoring'){continue;}
?><label for="<?= $key ?>"><?= e($label) ?>
<?php if($key==='status'): ?><select id="status" name="status"><?php foreach(pkl_schedule_statuses() as $value=>$statusLabel): ?><option value="<?= $value ?>" <?= $data['status']===$value?'selected':'' ?>><?= e($statusLabel) ?></option><?php endforeach; ?></select>
<?php elseif(in_array($key,['alamat_lengkap','agenda','hasil_monitoring','tindak_lanjut'],true)): ?><textarea id="<?= $key ?>" name="<?= $key ?>" rows="3" maxlength="<?= $max ?>" <?= in_array($key,['alamat_lengkap','agenda'],true)?'required':'' ?>><?= e($data[$key]) ?></textarea>
<?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= ['tanggal'=>'date','jam'=>'time'][$key]??'text' ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= $key==='orang_tua_wali'?'':'required' ?> <?= $key==='tanggal'&&$kind==='monitoring'?'min="'.e($placement['tanggal_mulai']).'" max="'.e($placement['tanggal_selesai']).'"':'' ?>><?php endif; ?></label><?php endforeach; ?>
<?php if($kind==='monitoring'): ?><p>Isi hasil monitoring saat status Selesai. Catat tindak lanjut jika diperlukan. Buat satu jadwal untuk setiap minggu kunjungan yang direncanakan.</p><?php endif; ?><button>Simpan jadwal</button></form><?php endif; ?>
<?php panel_end($user); ?>
