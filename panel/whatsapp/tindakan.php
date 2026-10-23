<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/whatsapp.php';
$user=require_role('admin','staf_bkk');require_post_csrf();
$id=positive_id($_POST['id']??'');$row=$id?panel_query('SELECT * FROM wa_recipients WHERE id=:id',['id'=>$id])->fetch():null;
if(!$row){panel_not_found();}
$action=$_POST['action']??'';
if($action==='open'){
    if(!wa_eligible($row)){http_response_code(409);exit('Penerima tidak lagi memenuhi status penerima atau telah meminta berhenti. Buat daftar baru setelah memeriksa data penerima.');}
    $phone=wa_phone($row['nomor']);if(!$phone){http_response_code(422);exit('Nomor tidak valid.');}
    panel_query('UPDATE wa_recipients SET opened_at=:at,opened_by=:user WHERE id=:id',['at'=>date('Y-m-d H:i:s'),'user'=>(int)$user['id'],'id'=>$id]);
    header('Referrer-Policy: no-referrer');
    header('Location: https://wa.me/'.$phone.'?text='.rawurlencode($row['pesan']),true,303);exit;
}
if(!in_array($action,['mark','unmark'],true)){http_response_code(422);exit('Tindakan tidak valid.');}
if($action==='mark'&&!$row['opened_at']){panel_message('Buka WhatsApp terlebih dahulu sebelum menandai pesan.','panel/whatsapp/detail.php?id='.$row['campaign_id']);}
panel_query('UPDATE wa_recipients SET marked_at=:at,marked_by=:user WHERE id=:id',['at'=>$action==='mark'?date('Y-m-d H:i:s'):null,'user'=>$action==='mark'?(int)$user['id']:null,'id'=>$id]);
redirect('panel/whatsapp/detail.php?id='.$row['campaign_id'].'&page='.(positive_id($_POST['page']??'1')??1),303);
