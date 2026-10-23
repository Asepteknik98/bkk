<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin');require_post_csrf();$data=[];$uploads=[];
try{
    foreach(settings_fields() as $key=>[$label,$max,$required]){
        $value=input_text($_POST,$key);$data[$key]=$value;
        if(!valid_text($value,$max,$required)){throw new InvalidArgumentException($label.' tidak valid atau terlalu panjang.');}
        if($value!==''&&str_starts_with($key,'social_')&&!http_url($value)){throw new InvalidArgumentException($label.' harus URL http/https.');}
        if($key==='public_announcement_link_url'&&$value!==''&&!http_url($value)){throw new InvalidArgumentException('URL hyperlink pengumuman harus menggunakan URL http/https yang valid.');}
        if($key==='contact_email'&&$value!==''&&!filter_var($value,FILTER_VALIDATE_EMAIL)){throw new InvalidArgumentException('Email kontak tidak valid.');}
    }
    if(($data['public_announcement_link_text']??'')!=='' xor ($data['public_announcement_link_url']??'')!==''){throw new InvalidArgumentException('Isi teks tombol dan URL hyperlink pengumuman secara bersamaan.');}
    foreach(['bkk_logo','school_logo','public_announcement_image'] as $field){$uploads[$field]=management_upload($field);}
    management_transaction(static function()use($data,$uploads,$user):void{
        $values=$data;
        foreach(['bkk_logo','school_logo','public_announcement_image'] as $field){
            $old=panel_query('SELECT setting_value FROM settings WHERE setting_key=:key FOR UPDATE',['key'=>$field])->fetchColumn();
            $values[$field]=management_image($field,$old===false?null:$old,(int)$user['id'],$uploads[$field]);
        }
        foreach($values as $key=>$value){panel_query('INSERT INTO settings(setting_key,setting_value) VALUES(:key,:value) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',['key'=>$key,'value'=>$value===''?null:$value]);}
    });
    unset($_SESSION['settings_form']);panel_message('Pengaturan berhasil disimpan.','panel/pengaturan/');
} catch (Throwable $exception) {
    foreach ($uploads as $upload) { discard_upload($upload); }
    $_SESSION['settings_form'] = [];
    foreach (array_keys(settings_fields()) as $key) { $_SESSION['settings_form'][$key] = input_text($_POST, $key); }
    management_error($exception, 'panel/pengaturan/');
}
