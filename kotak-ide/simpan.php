<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/industry-ideas.php';
require_once APP_ROOT.'/app/helpers/login-throttle.php';
require_post_csrf();industry_ideas_require_ready();$link=industry_idea_require_link();
if(!industry_idea_form_matches(input_text($_POST,'form_key'),$link['token_hash'],$_SESSION['industry_idea_bindings']??[])){http_response_code(403);exit('Formulir tidak sesuai tautan atau sudah kedaluwarsa. Buka kembali formulir mitra yang benar.');}
$data=industry_idea_input($_POST);$consent=($_POST['persetujuan']??'')==='1';$errors=industry_idea_errors($data,$consent);
if(($_POST['company_website']??null)!==''){$errors[]='Pemeriksaan formulir tidak valid. Muat ulang dan kirim kembali.';}
if(!$errors){
    $retry=max(login_attempt('industry-ideas-ip:'.(string)($_SERVER['REMOTE_ADDR']??'unknown'),30),login_attempt('industry-ideas-link:'.$link['token_hash'],5));
    if($retry>0){http_response_code(429);header('Retry-After: '.$retry);exit('Terlalu banyak pengiriman. Tunggu beberapa saat lalu kirim kembali.');}
    try{
        industry_idea_submit($link['token_hash'],$data,$consent);
        unset($_SESSION['industry_idea_hash'],$_SESSION['industry_idea_form'],$_SESSION['industry_idea_bindings']);$_SESSION['industry_idea_receipt']=time()+1800;redirect('kotak-ide/berhasil.php',303);
    }catch(InvalidArgumentException $exception){http_response_code(410);exit('Tautan tidak berlaku lagi. Hubungi BKK untuk tautan baru.');}
    catch(Throwable $exception){error_log('Pengiriman ide industri gagal: '.get_class($exception));$errors[]='Usulan belum dapat disimpan. Silakan coba kembali dengan tautan yang sama.';}
}
$_SESSION['industry_idea_form']=['data'=>$data,'errors'=>$errors];redirect('kotak-ide/',303);
