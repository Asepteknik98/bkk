<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/whatsapp.php';
$user=require_role('admin','staf_bkk');require_post_csrf();
$filters=wa_filters($_POST);$title=input_text($_POST,'judul');$message=input_text($_POST,'pesan');$link=input_text($_POST,'tautan');
$key=input_text($_POST,'submission_key');
if(!preg_match('/^[a-f0-9]{64}$/D',$key)||!hash_equals($_SESSION['wa_create_key']??'',$key)){http_response_code(403);exit('Formulir kedaluwarsa. Muat ulang halaman.');}
$existing=panel_query('SELECT id FROM wa_campaigns WHERE submission_key=:key',['key'=>$key])->fetchColumn();
if($existing){redirect('panel/whatsapp/detail.php?id='.$existing,303);}
try {
    if(!valid_text($title,150)||!valid_text($message,1500)||($link!==''&&!http_url($link))){throw new InvalidArgumentException('Isi judul dan pesan yang valid. Tautan opsional harus berupa URL http/https.');}
    $contacts=wa_contacts($filters);$mode=$_POST['mode']??'';
    if($mode==='manual'){
        $selected=$_POST['selected']??[];
        if(!is_array($selected)||count($selected)>20||array_filter($selected,static fn($v)=>!is_string($v))){throw new InvalidArgumentException('Pilihan penerima tidak valid.');}
        $contacts=array_filter($contacts,static fn($c)=>in_array($c['key'],$selected,true));
    }elseif($mode!=='all'){throw new InvalidArgumentException('Pilih cara menentukan penerima.');}
    if(!$contacts){throw new InvalidArgumentException('Tidak ada penerima dengan nomor valid dan persetujuan WhatsApp.');}
    db()->beginTransaction();
    panel_query('INSERT INTO wa_campaigns(judul,pesan,tautan,created_by,created_at,submission_key) VALUES(:judul,:pesan,:tautan,:user,:at,:key)',['judul'=>$title,'pesan'=>$message,'tautan'=>$link,'user'=>(int)$user['id'],'at'=>date('Y-m-d H:i:s'),'key'=>$key]);
    $id=(int)db()->lastInsertId();
    foreach($contacts as $contact){panel_query('INSERT INTO wa_recipients(campaign_id,source_type,source_id,nama,nomor,pesan) VALUES(:campaign,:type,:source,:nama,:nomor,:pesan)',['campaign'=>$id,'type'=>$contact['source_type'],'source'=>$contact['source_id'],'nama'=>$contact['nama'],'nomor'=>$contact['nomor'],'pesan'=>wa_message($message,$contact['nama'],$link)]);}
    db()->commit();unset($_SESSION['wa_draft']);
    redirect('panel/whatsapp/detail.php?id='.$id,303);
}catch(Throwable $exception){
    if(db()->inTransaction()){db()->rollBack();}
    $_SESSION['wa_draft']=['judul'=>$title,'pesan'=>$message,'tautan'=>$link];
    $error=$exception instanceof InvalidArgumentException?$exception->getMessage():'Daftar pesan belum dapat disimpan. Silakan coba kembali.';
    if(!$exception instanceof InvalidArgumentException){error_log('Daftar WA gagal: '.get_class($exception).'; kode='.$exception->getCode());}
    panel_message($error,'panel/whatsapp/?'.http_build_query($filters));
}
