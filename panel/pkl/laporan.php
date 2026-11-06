<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
$user=require_role('admin','staf_bkk');pkl_require_ready();$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$placement=pkl_find($id);}catch(InvalidArgumentException){panel_not_found();}
$report=panel_query('SELECT * FROM pkl_final_reports WHERE placement_id=:id',['id'=>$id])->fetch();
$data=['tanggal_laporan'=>$report['tanggal_laporan']??max(date('Y-m-d'),$placement['tanggal_mulai'])];foreach(pkl_report_fields() as $key=>$label){$data[$key]=$report[$key]??'';}
$version=(int)($report['version']??0);$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();foreach($data as $key=>$value){$data[$key]=input_text($_POST,$key);}
    $version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{pkl_report_save($id,$version,$data,(int)$user['id']);panel_message('Laporan akhir PKL berhasil disimpan.','panel/pkl/laporan.php?id='.$id);}
    catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
panel_start($user,'Laporan akhir PKL','pkl');
?>
<div class="page-actions"><h1>Laporan akhir PKL</h1><a href="<?= e(base_url('panel/pkl/detail.php?id='.$id)) ?>">Kembali ke jurnal</a><?php if($report): ?><a class="button" href="<?= e(base_url('panel/pkl/unduh.php?id='.$id.'&jenis=laporan')) ?>">Unduh laporan HTML / cetak</a><?php endif; ?></div>
<p><?= e($placement['nama'].' — '.$placement['tempat_pkl']) ?> · <?= e($placement['tanggal_mulai'].' – '.$placement['tanggal_selesai']) ?></p>
<p>Unduhan memuat identitas penempatan, penugasan pembimbing, narasi laporan, rekap mingguan, dan jurnal terbaru. Buka berkas HTML untuk mencetak atau menyimpan sebagai PDF melalui browser.</p>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<label for="tanggal_laporan">Tanggal laporan<input id="tanggal_laporan" name="tanggal_laporan" type="date" min="<?= e($placement['tanggal_mulai']) ?>" value="<?= e($data['tanggal_laporan']) ?>" required></label>
<?php foreach(pkl_report_fields() as $key=>$label): ?><label for="<?= $key ?>"><?= e($label) ?><textarea id="<?= $key ?>" name="<?= $key ?>" maxlength="10000" rows="4" <?= in_array($key,['kendala','saran'],true)?'':'required' ?>><?= e($data[$key]) ?></textarea></label><?php endforeach; ?><button>Simpan laporan akhir</button></form>
<?php panel_end($user); ?>
