<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/whatsapp.php';
$user=require_role('admin','staf_bkk');require_post_csrf();
$filters=wa_filters($_POST);$action=$_POST['action']??'update';
$selected=$_SESSION['wa_selected']??[];
if($action==='clear'){$selected=[];}
elseif($action==='add_all'){
    foreach(wa_contacts($filters) as $contact){$selected[$contact['nomor']]=$contact['key'];}
}elseif($action==='update'){
    $visible=$_POST['visible']??[];$checked=$_POST['selected']??[];
    if(!is_array($visible)||!is_array($checked)||count($visible)>20||count($checked)>20||array_filter(array_merge($visible,$checked),static fn($v)=>!is_string($v))){http_response_code(422);exit('Pilihan tidak valid.');}
    foreach(wa_contacts($filters,true) as $contact){
        if(!in_array($contact['key'],$visible,true)){continue;}
        if($contact['selectable']&&in_array($contact['key'],$checked,true)){$selected[$contact['nomor']]=$contact['key'];}
        else{unset($selected[$contact['nomor']]);}
    }
}else{http_response_code(422);exit('Tindakan tidak valid.');}
$_SESSION['wa_selected']=$selected;
if(isset($_POST['judul'],$_POST['pesan'],$_POST['tautan'])){
    $draft=[];foreach(['judul'=>150,'pesan'=>1500,'tautan'=>2048] as $key=>$max){$value=input_text($_POST,$key);if(valid_text($value,$max,false)){$draft[$key]=$value;}}
    if(count($draft)===3){$_SESSION['wa_draft']=$draft;}
}
$count=count(wa_selected_contacts());
if(($_POST['format']??'')==='json'){
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['count'=>$count,'phones'=>array_keys($_SESSION['wa_selected'])],JSON_THROW_ON_ERROR);exit;
}
redirect('panel/whatsapp/?'.http_build_query($filters+['page'=>positive_id($_POST['page']??'1')??1]),303);
