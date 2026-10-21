<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/programs.php';
$user=require_role('admin','staf_bkk');require_post_csrf();$rawId=$_POST['id']??'';$id=$rawId===''?null:positive_id($rawId);
if($rawId!==''&&$id===null){panel_not_found();}if($id&&!panel_query('SELECT id FROM program WHERE id=:id',['id'=>$id])->fetch()){panel_not_found();}
$data=program_input($_POST);$errors=program_errors($data);$upload=null;
if(!$errors){try{
    if(isset($_FILES['gambar'])&&!is_array($_FILES['gambar'])){throw new InvalidArgumentException('Data gambar tidak valid.');}
    $upload=store_upload($_FILES['gambar']??null,'program');$savedId=save_program($data,$id,(int)$user['id'],$upload);
    unset($_SESSION['program_form']);panel_message('Program berhasil disimpan.','panel/program/edit.php?id='.$savedId);
}catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}catch(Throwable $exception){error_log('Simpan program gagal: '.get_class($exception).' '.$exception->getCode());$errors[]='Program belum dapat disimpan. Periksa koneksi database dan izin unggahan, lalu coba kembali.';}}
discard_upload($upload);$_SESSION['program_form']=['id'=>$id,'data'=>$data,'errors'=>$errors];redirect($id?'panel/program/edit.php?id='.$id:'panel/program/tambah.php',303);
