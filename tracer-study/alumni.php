<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/alumni-links.php';
header('Referrer-Policy: no-referrer');header('Cache-Control: no-store');
if(array_key_exists('token',$_GET)){
    $token=is_string($_GET['token'])?$_GET['token']:'';$hash=alumni_link_hash($token);
    unset($_SESSION['alumni_link_hash'],$_SESSION['alumni_form']);
    if(!$hash||!alumni_link_find($hash)){http_response_code(410);exit('Tautan alumni tidak berlaku. Hubungi BKK.');}
    $_SESSION['alumni_link_hash']=$hash;
    redirect('tracer-study/alumni.php',303);
}
$alumniLink=require_alumni_link();
require __DIR__.'/formulir.php';
