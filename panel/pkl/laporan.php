<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
$user=require_role('admin','staf_bkk');pkl_require_ready();$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$placement=pkl_find($id);}catch(InvalidArgumentException){panel_not_found();}
$report=panel_query('SELECT * FROM pkl_final_reports WHERE placement_id=:id',['id'=>$id])->fetch();
$data=['tanggal_laporan'=>$report['tanggal_laporan']??(date('Y-m-d')>=$placement['tanggal_mulai']?date('Y-m-d'):'')];foreach(pkl_report_fields() as $key=>$label){$data[$key]=$report[$key]??'';}
$version=(int)($report['version']??0);$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();foreach($data as $key=>$value){$data[$key]=input_text($_POST,$key);}
    $version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{pkl_report_save($id,$version,$data,(int)$user['id']);panel_message('Laporan akhir PKL berhasil disimpan.','panel/pkl/laporan.php?id='.$id);}
    catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
panel_start($user,'Laporan akhir PKL','pkl');
?>
<div class="pkl-report-workspace">
<header class="page-actions"><div><h1><?= panel_icon('lamaran') ?> Laporan akhir PKL</h1><p>Lengkapi hasil pelaksanaan dan evaluasi siswa.</p></div><nav aria-label="Tindakan laporan"><a href="<?= e(base_url('panel/pkl/detail.php?id='.$id)) ?>"><?= panel_icon('back') ?> Kembali ke jurnal</a><?php if($report): ?><a href="<?= e(base_url('panel/pkl/unduh.php?id='.$id.'&jenis=laporan')) ?>"><?= panel_icon('download') ?> Unduh / cetak</a><?php endif; ?></nav></header>
<div class="pkl-report-identity"><strong><?= e($placement['nama']) ?></strong><span><?= e($placement['tempat_pkl']) ?></span><span><?= e($placement['tanggal_mulai'].' - '.$placement['tanggal_selesai']) ?></span></div>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form pkl-report-form" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<div class="pkl-report-grid">
<section class="pkl-report-column"><h2><?= panel_icon('lamaran') ?> Pelaksanaan &amp; hasil</h2>
<label for="tanggal_laporan"><span><?= panel_icon('calendar') ?> Tanggal laporan *</span><input id="tanggal_laporan" name="tanggal_laporan" type="date" min="<?= e($placement['tanggal_mulai']) ?>" value="<?= e($data['tanggal_laporan']) ?>" required></label>
<?php foreach(['ringkasan','hasil'] as $key): ?><label for="<?= $key ?>"><span><?= panel_icon('lamaran') ?> <?= e(pkl_report_fields()[$key]) ?> *</span><textarea id="<?= $key ?>" name="<?= $key ?>" maxlength="10000" rows="4" required><?= e($data[$key]) ?></textarea></label><?php endforeach; ?>
</section>
<section class="pkl-report-column"><h2><?= panel_icon('message') ?> Evaluasi &amp; kesimpulan</h2>
<?php foreach(['kendala','saran','kesimpulan'] as $key): ?><label for="<?= $key ?>"><span><?= panel_icon('message') ?> <?= e(pkl_report_fields()[$key]) ?><?= $key==='kesimpulan'?' *':'' ?></span><textarea id="<?= $key ?>" name="<?= $key ?>" maxlength="10000" rows="<?= $key==='kesimpulan'?'4':'3' ?>" <?= $key==='kesimpulan'?'required':'' ?>><?= e($data[$key]) ?></textarea></label><?php endforeach; ?>
</section></div>
<footer class="pkl-report-footer"><small>* Wajib diisi. Status penempatan diubah melalui Edit penempatan. Unduhan tersedia setelah laporan disimpan.</small><button type="submit"><?= panel_icon('save') ?> Simpan laporan</button></footer>
</form>
</div>
<?php panel_end($user); ?>
