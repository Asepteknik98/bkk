<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-management.php';
$user=require_role('admin','staf_bkk');
if(!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','POST'],true)){header('Allow: GET, POST');http_response_code(405);exit('Metode tidak diizinkan.');}
$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$old=tracer_record($id);}catch(InvalidArgumentException){panel_not_found();}
$editing=true;$errors=[];$data=tracer_input(array_map(static fn($v)=>(string)($v??''),$old));
$data['persetujuan_data']=(bool)$old['persetujuan_data'];$revision=tracer_revision($old);
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=tracer_input($_POST);$data['persetujuan_data']=(bool)$old['persetujuan_data'];
    $revision=input_text($_POST,'revision');
    try{tracer_admin_update($id,$_POST,$revision);panel_message('Data alumni berhasil diperbarui.','panel/tracer-study/detail.php?id='.$id);}
    catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
    catch(Throwable $exception){error_log('Edit tracer gagal: '.get_class($exception));$errors[]='Data belum dapat diperbarui. Silakan coba kembali.';}
}
require __DIR__.'/form.php';
