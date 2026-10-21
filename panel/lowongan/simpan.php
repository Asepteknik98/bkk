<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/vacancies.php';
$user=require_role('admin','staf_bkk');require_post_csrf();
$rawId=$_POST['id']??'';$id=$rawId===''?null:positive_id($rawId);
if($rawId!==''&&$id===null){panel_not_found();}
if($id&&!panel_query('SELECT id FROM lowongan WHERE id=:id',['id'=>$id])->fetch()){panel_not_found();}
$data=vacancy_input($_POST);$errors=vacancy_errors($data);$upload=null;
if(!$errors){try{
    if(isset($_FILES['gambar'])&&!is_array($_FILES['gambar'])){throw new InvalidArgumentException('Data poster tidak valid.');}
    $upload=store_upload($_FILES['gambar']??null,'lowongan');$savedId=save_vacancy($data,$id,(int)$user['id'],$upload);
    unset($_SESSION['vacancy_form']);panel_message('Lowongan berhasil disimpan.','panel/lowongan/edit.php?id='.$savedId);
}catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
catch(Throwable $exception){error_log('Simpan lowongan gagal: '.get_class($exception).' '.$exception->getCode());$errors[]='Lowongan belum dapat disimpan. Periksa koneksi database dan izin unggahan, lalu coba kembali.';}}
discard_upload($upload);$_SESSION['vacancy_form']=['id'=>$id,'data'=>$data,'errors'=>$errors];redirect($id?'panel/lowongan/edit.php?id='.$id:'panel/lowongan/tambah.php',303);
