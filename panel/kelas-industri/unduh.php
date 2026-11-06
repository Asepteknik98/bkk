<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-classes.php';
require_once APP_ROOT.'/app/helpers/excel.php';
$user=require_role('admin','staf_bkk');industry_require_ready();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
db()->beginTransaction();
try{
    try{$class=industry_find($id);}catch(InvalidArgumentException){db()->rollBack();panel_not_found();}
    $names=panel_query('SELECT s.nama FROM speakers s JOIN industry_class_speakers r ON r.speaker_id=s.id WHERE r.class_id=:id ORDER BY s.nama,s.id',['id'=>$id])->fetchAll(PDO::FETCH_COLUMN);$class['pemateri']=implode(', ',$names);
    $meetings=panel_query('SELECT a.*,s.nama AS pemateri FROM industry_class_meetings a LEFT JOIN speakers s ON s.id=a.pemateri_id WHERE a.class_id=:id ORDER BY a.urutan,a.id',['id'=>$id])->fetchAll();
    $class['terlaksana']=count(array_filter($meetings,static fn($row)=>$row['status']==='terlaksana'));
    foreach($meetings as &$meeting){$meeting['status']=industry_meeting_statuses()[$meeting['status']];$meeting['jam']=$meeting['jam']?substr($meeting['jam'],0,5):'';$meeting['pemateri']=$meeting['pemateri']?:$class['pemateri'];}unset($meeting);
    $participants=panel_query('SELECT s.nis,s.nama,s.kelas,s.jurusan FROM siswa s JOIN industry_class_participants r ON r.siswa_id=s.id WHERE r.class_id=:id ORDER BY s.nama,s.id',['id'=>$id])->fetchAll();
    db()->commit();
}catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
excel_download('kelas-industri-'.$id.'.xlsx',[
    ['name'=>'Kelas Industri','columns'=>['nama'=>'Kelas industri','mitra_nama'=>'Mitra industri','pemateri'=>'Pemateri kelas','kelas_sasaran'=>'Sasaran','rencana_pertemuan'=>'Rencana pertemuan','terlaksana'=>'Terlaksana','status'=>'Status kelas','catatan'=>'Catatan'],'rows'=>[$class]],
    ['name'=>'Pertemuan','columns'=>['urutan'=>'Pertemuan ke','tanggal'=>'Tanggal','jam'=>'Jam WIB','lokasi'=>'Lokasi','pemateri'=>'Pemateri','materi'=>'Materi','status'=>'Status','catatan'=>'Catatan'],'rows'=>$meetings],
    ['name'=>'Peserta','columns'=>['nis'=>'NIS','nama'=>'Nama','kelas'=>'Kelas','jurusan'=>'Jurusan'],'rows'=>$participants],
]);
