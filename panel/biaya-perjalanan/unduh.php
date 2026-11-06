<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl-schedules.php';
require_once APP_ROOT.'/app/helpers/excel.php';
$user=require_role('admin','staf_bkk');pkl_schedules_require_ready();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
try{[$filters,$where,$params]=pkl_schedule_filters($_GET,true);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$rows=panel_query('SELECT a.*,p.tanggal_mulai,p.tanggal_selesai,s.nama,s.nis FROM pkl_business_trips a JOIN pkl_placements p ON p.id=a.placement_id JOIN siswa s ON s.id=p.siswa_id WHERE '.$where.' ORDER BY a.tanggal,a.id',$params)->fetchAll();
header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
excel_download('perjalanan-dinas-pkl.xlsx',[['name'=>'Perjalanan dinas','columns'=>['nama'=>'Siswa terkait','nis'=>'NIS','tanggal_mulai'=>'Mulai PKL','tanggal_selesai'=>'Selesai PKL','monitoring_id'=>'ID monitoring (opsional)','tujuan'=>'Tujuan','petugas'=>'Petugas','tanggal'=>'Tanggal perjalanan','nominal'=>'Nominal rupiah (desimal)','sumber_biaya'=>'Sumber biaya'],'rows'=>$rows]]);
