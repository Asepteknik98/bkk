<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
require_once APP_ROOT.'/app/helpers/excel.php';
$user=require_role('admin','staf_bkk');pkl_require_ready();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
$id=positive_id($_GET['id']??'');$type=input_text($_GET,'jenis');if(!$id||!in_array($type,['rekap','laporan'],true)){panel_not_found();}
db()->beginTransaction();
try{
    try{$placement=pkl_find($id);}catch(InvalidArgumentException){db()->rollBack();panel_not_found();}
    $journals=panel_query('SELECT tanggal,kegiatan,catatan FROM pkl_journals WHERE placement_id=:id ORDER BY tanggal,id',['id'=>$id])->fetchAll();$weeks=pkl_weekly($journals);
    $report=panel_query('SELECT * FROM pkl_final_reports WHERE placement_id=:id',['id'=>$id])->fetch();
    db()->commit();
}catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
if($type==='rekap'){
    $summary=[];foreach($weeks as $week){$week['kegiatan']=implode("\n",$week['kegiatan']);$week['catatan']=implode("\n",$week['catatan']);$summary[]=$week;}
    excel_download('rekap-pkl-'.$id.'.xlsx',[
        ['name'=>'Penempatan','columns'=>['nama'=>'Siswa','nis'=>'NIS']+array_map(static fn($meta)=>$meta[0],pkl_fields()),'rows'=>[$placement]],
        ['name'=>'Rekap mingguan','columns'=>['minggu'=>'Minggu ISO','mulai'=>'Senin','selesai'=>'Minggu','hari'=>'Hari dengan jurnal','kegiatan'=>'Kegiatan','catatan'=>'Catatan'],'rows'=>$summary],
        ['name'=>'Jurnal harian','columns'=>['tanggal'=>'Tanggal','kegiatan'=>'Kegiatan','catatan'=>'Catatan'],'rows'=>$journals],
    ]);exit;
}
if(!$report){http_response_code(404);exit('Laporan akhir belum disimpan.');}
header('Content-Type: text/html; charset=UTF-8');header('Content-Disposition: attachment; filename="laporan-pkl-'.$id.'.html"');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; form-action 'none'");
require APP_ROOT.'/app/layouts/pkl-report.php';
