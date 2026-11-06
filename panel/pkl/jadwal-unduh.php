<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl-schedules.php';
require_once APP_ROOT.'/app/helpers/excel.php';
$user=require_role('admin','staf_bkk');pkl_schedules_require_ready();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
try{[$filters,$where,$params]=pkl_schedule_filters($_GET);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$rows=panel_query('SELECT a.*,p.tanggal_mulai,p.tanggal_selesai,s.nama,s.nis FROM pkl_schedules a JOIN pkl_placements p ON p.id=a.placement_id JOIN siswa s ON s.id=p.siswa_id WHERE '.$where.' ORDER BY a.tanggal,a.jam,a.id',$params)->fetchAll();
foreach($rows as &$row){$row['jenis']=pkl_schedule_types()[$row['jenis']];$row['status']=pkl_schedule_statuses()[$row['status']];$row['jam']=substr($row['jam'],0,5);$row['minggu']=(new DateTimeImmutable($row['tanggal']))->format('o-\WW');}unset($row);
header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
excel_download('jadwal-pkl.xlsx',[['name'=>'Jadwal PKL','columns'=>['nama'=>'Siswa','nis'=>'NIS','tanggal_mulai'=>'Mulai PKL','tanggal_selesai'=>'Selesai PKL','jenis'=>'Jenis jadwal','minggu'=>'Minggu ISO']+array_map(static fn($meta)=>$meta[0],pkl_schedule_fields()),'rows'=>$rows]]);
