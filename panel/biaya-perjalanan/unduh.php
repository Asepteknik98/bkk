<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl-schedules.php';
require_once APP_ROOT.'/app/helpers/excel.php';
$user=require_role('admin','staf_bkk');pkl_schedules_require_ready();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
try{[$filters,$where,$params]=pkl_schedule_filters($_GET,true);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$from=' FROM pkl_business_trips a JOIN pkl_placements p ON p.id=a.placement_id JOIN siswa s ON s.id=p.siswa_id WHERE '.$where;
$rows=panel_query('SELECT a.*,p.tanggal_mulai,p.tanggal_selesai,s.nama'.$from.' ORDER BY a.tanggal,a.id',$params)->fetchAll();
$summary=panel_query("SELECT COUNT(*) AS jumlah,COALESCE(SUM(a.nominal),0) AS total,COALESCE(SUM(CASE WHEN a.sumber_biaya='sekolah' THEN a.nominal ELSE 0 END),0) AS sekolah,COALESCE(SUM(CASE WHEN a.sumber_biaya='pribadi' THEN a.nominal ELSE 0 END),0) AS pribadi".$from,$params)->fetch();
$rekap=[
    ['keterangan'=>'Biaya dari sekolah','nilai'=>pkl_rupiah((string)$summary['sekolah'])],
    ['keterangan'=>'Biaya pribadi','nilai'=>pkl_rupiah((string)$summary['pribadi'])],
    ['keterangan'=>'Total biaya','nilai'=>pkl_rupiah((string)$summary['total'])],
    ['keterangan'=>'Jumlah catatan perjalanan','nilai'=>(int)$summary['jumlah']],
    ['keterangan'=>'Periode penempatan PKL','nilai'=>$filters['periode']!==''?str_replace('_',' sampai ',$filters['periode']):'Semua periode'],
    ['keterangan'=>'Tanggal perjalanan','nilai'=>$filters['minggu']!==''?$params['senin'].' sampai '.$params['minggu']:'Semua tanggal'],
    ['keterangan'=>'Penempatan PKL','nilai'=>$filters['placement_id']!==''?'#'.$filters['placement_id']:'Semua penempatan'],
    ['keterangan'=>'Cakupan','nilai'=>'Seluruh catatan sesuai filter, bukan hanya halaman yang tampil.'],
];
header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
excel_download('rekap-biaya-perjalanan-pkl.xlsx',[
    ['name'=>'Rekap biaya','columns'=>['keterangan'=>'Keterangan','nilai'=>'Nilai'],'rows'=>$rekap],
    ['name'=>'Rincian perjalanan','columns'=>['nama'=>'Siswa terkait','tanggal_mulai'=>'Mulai PKL','tanggal_selesai'=>'Selesai PKL','tujuan'=>'Tujuan perjalanan','petugas'=>'Petugas','tanggal'=>'Tanggal perjalanan','nominal'=>'Nominal rupiah (desimal)','sumber_biaya'=>'Sumber biaya','monitoring_id'=>'ID monitoring (opsional)'],'rows'=>$rows],
]);
