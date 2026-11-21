<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl-schedules.php';
$user=require_role('admin','staf_bkk');pkl_schedules_require_ready();
$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=$id?panel_query('SELECT * FROM pkl_business_trips WHERE id=:id',['id'=>$id])->fetch():null;if($id&&!$old){panel_not_found();}
$placementId=$old?(int)$old['placement_id']:(positive_id($_GET['placement_id']??'')??0);$placement=null;
if($placementId){try{$placement=pkl_find($placementId);}catch(InvalidArgumentException){panel_not_found();}}
$data=$old?pkl_trip_input(array_map(static fn($value)=>(string)($value??''),$old)):pkl_trip_input([]);$version=(int)($old['version']??0);$errors=[];
$monitoring=$placement?panel_query("SELECT id,tanggal,jam,petugas,tujuan,status FROM pkl_schedules WHERE placement_id=:id AND jenis='monitoring' ORDER BY tanggal,id",['id'=>$placementId])->fetchAll():[];
if(!$old&&$placement){
    $data=array_replace($data,['tujuan'=>$placement['tempat_pkl'],'petugas'=>$placement['guru_pembimbing'],'tanggal'=>'']);
    if(isset($_GET['monitoring_id'])){
        $monitorId=positive_id($_GET['monitoring_id']);$match=null;foreach($monitoring as $visit){if((int)$visit['id']===$monitorId){$match=$visit;break;}}
        if(!$match){panel_not_found();}$data=array_replace($data,['monitoring_id'=>(string)$match['id'],'tujuan'=>$match['tujuan'],'petugas'=>$match['petugas'],'tanggal'=>$match['tanggal']]);
    }
}
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();if(!$placement){http_response_code(400);exit('Pilih penempatan terlebih dahulu.');}
    $data=pkl_trip_input($_POST);$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{pkl_trip_save($placementId,$id,$version,$data,(int)$user['id']);panel_message('Perjalanan dinas berhasil disimpan.','panel/biaya-perjalanan/?placement_id='.$placementId);}
    catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
panel_start($user,$id?'Edit perjalanan dinas':'Catat perjalanan dinas','biaya-perjalanan');
?>
<div class="trip-workspace">
<header class="page-actions"><div><h1><?= panel_icon('edit') ?> <?= $id?'Edit':'Catat' ?> perjalanan dinas</h1><p><?= $placement?'Langkah 2: isi rincian perjalanan dan biaya.':'Langkah 1: pilih penempatan siswa yang terkait perjalanan.' ?></p></div><a href="<?= e(base_url('panel/biaya-perjalanan/')) ?>"><?= panel_icon('back') ?> Daftar perjalanan</a></header>
<?php if(!$placement):require APP_ROOT.'/app/layouts/trip-placement-picker.php';else: ?>
<div class="trip-context"><strong><?= e($placement['nama']) ?></strong><span><?= e($placement['tempat_pkl']) ?> &middot; Periode <?= e($placement['tanggal_mulai'].' - '.$placement['tanggal_selesai']) ?></span><?php if(!$id): ?><a href="<?= e(base_url('panel/biaya-perjalanan/form.php')) ?>">Ganti penempatan</a><?php endif; ?></div>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card trip-editor" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<h2>1. Rincian perjalanan</h2>
<div class="trip-cell trip-wide"><label for="monitoring_id">Monitoring terkait <small>Opsional</small></label><select id="monitoring_id" name="monitoring_id"><option value="">Perjalanan tanpa kaitan monitoring</option><?php foreach($monitoring as $visit): ?><option value="<?= $visit['id'] ?>" <?= (string)$visit['id']===$data['monitoring_id']?'selected':'' ?>><?= e($visit['tanggal'].' '.substr($visit['jam'],0,5).' - '.$visit['tujuan'].' - '.pkl_schedule_statuses()[$visit['status']]) ?></option><?php endforeach; ?></select><small>Hanya monitoring dari penempatan ini. Memilih kaitan tidak otomatis mengisi atau memperbarui rincian perjalanan; periksa tanggal, petugas, dan tujuan.</small></div>
<?php foreach(['tujuan'=>['Tujuan perjalanan',190],'petugas'=>['Nama petugas',250],'tanggal'=>['Tanggal perjalanan',10]] as $key=>[$label,$max]): ?><div class="trip-cell"><label for="<?= $key ?>"><?= e($label) ?> *</label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $key==='tanggal'?'date':'text' ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" required></div><?php endforeach; ?>
<h2>2. Biaya yang dicatat</h2>
<div class="trip-cell"><label for="nominal">Nominal biaya (Rp) *</label><input id="nominal" name="nominal" inputmode="decimal" maxlength="16" value="<?= e($data['nominal']) ?>" required aria-describedby="trip-money-help"><small id="trip-money-help">Tanpa titik ribuan. Contoh: 150000 atau 150000,50. Nilai 0 diperbolehkan.</small></div>
<div class="trip-cell"><label for="sumber_biaya">Sumber biaya *</label><select id="sumber_biaya" name="sumber_biaya" required><option value="">Pilih sumber biaya</option><?php foreach(['sekolah'=>'Sekolah','pribadi'=>'Pribadi'] as $key=>$label): ?><option value="<?= $key ?>" <?= $data['sumber_biaya']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></div>
<p class="trip-form-note">Nominal dihitung ke rekap sesuai sumber biaya. Bila perjalanan yang sama terkait beberapa siswa, jangan mengulang nominal penuh pada setiap siswa. Perubahan catatan ini tidak mengubah jadwal atau status monitoring.</p>
<div class="form-actions"><small>* Wajib diisi</small><button type="submit"><?= panel_icon('save') ?> Simpan perjalanan</button><a href="<?= e(base_url('panel/biaya-perjalanan/')) ?>">Batal</a></div>
</form><?php endif; ?>
</div>
<?php panel_end($user); ?>
