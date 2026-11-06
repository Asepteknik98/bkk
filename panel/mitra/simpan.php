<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/partners.php';
$user=require_role('admin','staf_bkk'); require_post_csrf();
$raw=$_POST['id']??''; $id=$raw===''?null:positive_id($raw); if($raw!==''&&!$id){panel_not_found();}
$data=[];foreach(['nama','jenis','website','deskripsi','aktif'] as $key){$data[$key]=input_text($_POST,$key);} $upload=null;
$profile=($_POST['profile_form']??'')==='1'?partner_profile_input($_POST):null;
$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
try {
    if($profile&&($_POST['profile_complete']??'')!=='1'){throw new InvalidArgumentException('Formulir profil tidak lengkap. Muat ulang sebelum menyimpan.');}
    $upload=management_upload('logo');
    $saved=partner_save($data,$profile,$version,$id,(int)$user['id'],$upload);
    unset($_SESSION['mitra_form']);panel_message('Mitra berhasil disimpan.','panel/mitra/detail.php?id='.$saved);
} catch(Throwable $exception){discard_upload($upload);$_SESSION['mitra_form']=['id'=>$id,'data'=>$data+($profile??[])+['version'=>$version]];management_error($exception,$id?'panel/mitra/edit.php?id='.$id:'panel/mitra/tambah.php');}
