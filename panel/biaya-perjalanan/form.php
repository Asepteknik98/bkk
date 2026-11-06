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
    $data=array_replace($data,['tujuan'=>$placement['tempat_pkl'],'petugas'=>$placement['guru_pembimbing'],'tanggal'=>$placement['tanggal_mulai']]);
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
<div class="page-actions"><h1><?= $id?'Edit':'Catat' ?> perjalanan dinas</h1><a href="<?= e(base_url('panel/biaya-perjalanan/')) ?>">Daftar perjalanan</a></div>
<?php if(!$placement):$pickerAction='panel/biaya-perjalanan/form.php';$pickerParams=[];require APP_ROOT.'/app/layouts/pkl-placement-picker.php';else: ?>
<p><strong><?= e($placement['nama']) ?></strong> · Periode PKL <?= e($placement['tanggal_mulai'].' – '.$placement['tanggal_selesai']) ?></p>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<label for="monitoring_id">Kaitan monitoring (opsional)<select id="monitoring_id" name="monitoring_id"><option value="">Tanpa kaitan monitoring</option><?php foreach($monitoring as $visit): ?><option value="<?= $visit['id'] ?>" <?= (string)$visit['id']===$data['monitoring_id']?'selected':'' ?>><?= e('#'.$visit['id'].' · '.$visit['tanggal'].' '.substr($visit['jam'],0,5).' · '.$visit['tujuan'].' · '.pkl_schedule_statuses()[$visit['status']]) ?></option><?php endforeach; ?></select></label>
<p>Tujuan, petugas, dan tanggal perjalanan disimpan sebagai catatan tersendiri. Perubahan jadwal monitoring tidak otomatis mengubah catatan perjalanan.</p>
<?php foreach(['tujuan'=>['Tujuan perjalanan',190],'petugas'=>['Petugas perjalanan',250],'tanggal'=>['Tanggal perjalanan',10],'nominal'=>['Nominal biaya (rupiah)',16]] as $key=>[$label,$max]): ?><label for="<?= $key ?>"><?= e($label) ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $key==='tanggal'?'date':'text' ?>" <?= $key==='nominal'?'inputmode="decimal"':'' ?> maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" required></label><?php endforeach; ?>
<p>Nominal tanpa pemisah ribuan, misalnya 150000 atau 150000,50. Tidak menerima nilai negatif. Nilai nol dapat dicatat.</p>
<label for="sumber_biaya">Sumber biaya<select id="sumber_biaya" name="sumber_biaya" required><option value="">Pilih sumber biaya</option><?php foreach(['sekolah'=>'Sekolah','pribadi'=>'Pribadi'] as $key=>$label): ?><option value="<?= $key ?>" <?= $data['sumber_biaya']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><button>Simpan perjalanan</button></form><?php endif; ?>
<?php panel_end($user); ?>
