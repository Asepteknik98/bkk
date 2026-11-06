<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-forums.php';require_once APP_ROOT.'/app/helpers/excel.php';
$user=require_role('admin','staf_bkk');industry_forums_require_ready();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
db()->beginTransaction();
try{
    try{$forum=industry_forum_find($id);}catch(InvalidArgumentException){db()->rollBack();panel_not_found();}
    $invites=panel_query('SELECT m.nama,i.pic_nama,i.pic_kontak FROM industry_forum_invitees i JOIN mitra m ON m.id=i.mitra_id WHERE i.forum_id=:id ORDER BY m.nama,m.id',['id'=>$id])->fetchAll();db()->commit();
}catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
excel_download('sarasehan-industri-'.$id.'.xlsx',[
    ['name'=>'Jadwal','columns'=>['nama'=>'Kegiatan','tanggal'=>'Tanggal','jam'=>'Jam WIB','lokasi'=>'Lokasi','tautan'=>'Tautan pertemuan','pic_sekolah'=>'PIC sekolah'],'rows'=>[$forum]],
    ['name'=>'Mitra undangan','columns'=>['nama'=>'Perusahaan','pic_nama'=>'PIC industri','pic_kontak'=>'Kontak PIC'],'rows'=>$invites],
]);
