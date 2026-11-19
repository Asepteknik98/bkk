<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/learning-reports.php';
$user=require_role('admin','staf_bkk');report_require_ready();
$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$report=report_find($id);}catch(InvalidArgumentException){panel_not_found();}
$reportRows=panel_query('SELECT * FROM student_learning_report_items WHERE report_id=:id ORDER BY urutan',['id'=>$id])->fetchAll();
panel_start($user,'Rapor '.$report['nama'],'capaian');
?>
<div class="learning-report-page"><div class="page-actions"><h1><?= panel_icon('certificate') ?> Rapor siswa</h1><div class="report-actions"><a class="button" href="<?= e(base_url('panel/capaian-pembelajaran/cetak.php?id='.$id)) ?>" target="_blank" rel="noopener"><?= panel_icon('lamaran') ?> Cetak / Simpan PDF</a><a href="<?= e(base_url('panel/capaian-pembelajaran/form.php?id='.$id)) ?>">Edit penilaian</a><a href="<?= e(base_url('panel/capaian-pembelajaran/')) ?>">Daftar rapor</a></div></div>
<?php require APP_ROOT.'/app/layouts/learning-report.php'; ?></div>
<?php panel_end($user); ?>
