<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/alumni-links.php';
require_post_csrf();$owner=require_alumni_link();
try{
    $errors=alumni_link_update($owner['token_hash'],$_POST);
    if(!$errors){unset($_SESSION['alumni_link_hash'],$_SESSION['alumni_form']);header('Content-Type: text/html; charset=UTF-8');echo '<h1>Data alumni berhasil diperbarui</h1><p>Tautan ini telah digunakan dan tidak dapat dibuka kembali.</p><a href="'.e(base_url()).'">Kembali ke beranda</a>';exit;}
}catch(InvalidArgumentException $exception){$errors=[$exception->getMessage()];}
$_SESSION['alumni_form']=['data'=>alumni_bound_input($_POST,$owner),'errors'=>$errors];
redirect('tracer-study/alumni.php',303);
