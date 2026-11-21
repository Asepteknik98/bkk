<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
require_once APP_ROOT.'/app/helpers/participants.php';
$user=require_role('admin');require_post_csrf();$data=[];$uploads=[];
$sections=settings_sections();$legacy=!array_key_exists('bagian',$_POST);
$sectionKey=$legacy?'legacy':($_POST['bagian']??null);
if(!$legacy&&(!is_string($sectionKey)||!isset($sections[$sectionKey]))){http_response_code(422);exit('Bagian pengaturan tidak valid.');}
$allowed=$legacy?array_keys(settings_fields()):$sections[$sectionKey]['fields'];
$logoFields=$legacy||$sectionKey==='logo'?['bkk_logo','school_logo']:[];
$returnPath='panel/pengaturan/'.($legacy?'':'?bagian='.$sectionKey);
try{
    $fields=settings_fields();
    foreach($allowed as $key){
        if($legacy&&!array_key_exists($key,$_POST)){continue;}
        [$label,$max,$required]=$fields[$key];
        $value=input_text($_POST,$key);$data[$key]=$value;
        if(in_array($key,['participant_classes','participant_departments','participant_years','participant_industries','participant_pkl_years'],true)){
            if(isset($_POST[$key])&&!is_string($_POST[$key])){throw new InvalidArgumentException($label.' harus berupa teks.');}
            $value=array_key_exists($key,$_POST)?$value:setting($key);
            $options=match($key){
                'participant_years'=>participant_year_options($value),
                'participant_industries'=>participant_industry_options($value),
                'participant_pkl_years'=>participant_pkl_year_options($value),
                default=>participant_option_lines($value,$key==='participant_classes'?80:150,$label),
            };
            $value=implode("\n",$options);
            $data[$key]=$value;
        }
        if(!valid_text($value,$max,$required)){throw new InvalidArgumentException($label.' tidak valid atau terlalu panjang.');}
        if($value!==''&&str_starts_with($key,'social_')&&!http_url($value)){throw new InvalidArgumentException($label.' harus URL http/https.');}
        if(in_array($key,['contact_email','manager_email'],true)&&$value!==''&&!filter_var($value,FILTER_VALIDATE_EMAIL)){throw new InvalidArgumentException('Email kontak tidak valid.');}
    }
    foreach($logoFields as $field){$uploads[$field]=management_upload($field);}
    management_transaction(static function()use($data,$uploads,$user,$logoFields):void{
        $values=$data;
        foreach($logoFields as $field){
            $old=panel_query('SELECT setting_value FROM settings WHERE setting_key=:key FOR UPDATE',['key'=>$field])->fetchColumn();
            $values[$field]=management_image($field,$old===false?null:$old,(int)$user['id'],$uploads[$field]);
        }
        foreach($values as $key=>$value){panel_query('INSERT INTO settings(setting_key,setting_value) VALUES(:key,:value) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',['key'=>$key,'value'=>$value===''?null:$value]);}
    });
    unset($_SESSION['settings_form']);panel_message('Pengaturan berhasil disimpan.',$returnPath);
} catch (Throwable $exception) {
    foreach ($uploads as $upload) { discard_upload($upload); }
    $_SESSION['settings_form']=['section'=>$sectionKey,'data'=>[]];
    foreach($allowed as $key){$_SESSION['settings_form']['data'][$key]=input_text($_POST,$key);}
    management_error($exception,$returnPath);
}
