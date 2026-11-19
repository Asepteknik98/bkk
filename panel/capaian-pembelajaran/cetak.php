<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/learning-reports.php';
require_once APP_ROOT.'/app/helpers/public.php';
$user=require_role('admin','staf_bkk');report_require_ready();
$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$report=report_find($id);}catch(InvalidArgumentException){panel_not_found();}
$reportRows=panel_query('SELECT * FROM student_learning_report_items WHERE report_id=:id ORDER BY urutan',['id'=>$id])->fetchAll();
header('Cache-Control: no-store');header('X-Robots-Tag: noindex, nofollow');
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e('Rapor - '.$report['nama']) ?></title><link rel="stylesheet" href="<?= e(asset_url('css/learning-reports.css')) ?>"></head><body class="report-print-page"><nav class="report-print-tools" aria-label="Cetak rapor"><button type="button" data-report-print>Cetak / Simpan PDF</button><a href="<?= e(base_url('panel/capaian-pembelajaran/detail.php?id='.$id)) ?>">Kembali</a></nav><?php require APP_ROOT.'/app/layouts/learning-report.php'; ?><script src="<?= e(asset_url('js/learning-reports.js')) ?>" defer></script></body></html>
