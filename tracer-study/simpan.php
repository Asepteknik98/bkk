<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/tracer.php';
require_once APP_ROOT.'/app/helpers/login-throttle.php';
require_post_csrf();
$nonce=is_string($_POST['submission_token']??null)?$_POST['submission_token']:'';
$data=tracer_input($_POST);$errors=tracer_errors($data);
$issued=$_SESSION['tracer_forms'][$nonce]??0;
if(!preg_match('/^[a-f0-9]{64}$/D',$nonce)||!is_int($issued)||$issued<time()-7200){$errors[]='Formulir sudah kedaluwarsa. Buka formulir baru untuk mengirim.';$nonce=tracer_nonce();}
if(!is_string($_POST['company_website']??null)||$_POST['company_website']!==''){$errors[]='Pemeriksaan anti-spam tidak valid. Muat ulang formulir dan biarkan kolom tersembunyi kosong.';}
$repeated=false;
if(!$errors){
    $key=hash('sha256',$nonce);
    try{
        $find=db()->prepare('SELECT * FROM tracer_responses WHERE submission_key=:key LIMIT 1');$find->execute(['key'=>$key]);$existing=$find->fetch();
        if($existing){
            if(!tracer_same_response($existing,$data)){throw new InvalidArgumentException('Formulir ini sudah pernah dikirim dengan isi berbeda. Buka formulir baru untuk mengirim pembaruan, atau hubungi BKK untuk koreksi.');}
            $repeated=true;
        }else{
            $ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');
            $retry=max(login_attempt('tracer-ip:'.$ip,30),login_attempt('tracer-session:'.session_id(),5));
            if($retry>0){http_response_code(429);header('Retry-After: '.$retry);exit('Terlalu banyak pengiriman. Tunggu sampai 15 menit, lalu kirim kembali formulir yang sama. Data belum disimpan.');}
            $params=[];foreach(tracer_fields() as $field=>$label){$params[$field]=$data[$field]===''?null:$data[$field];}
            $params['tahun_lulus']=(int)$data['tahun_lulus'];$params['submission_key']=$key;$params['versi_persetujuan']=TRACER_CONSENT_VERSION;
            $params['wa_consent']=(int)$data['wa_consent'];$params['wa_consent_at']=$data['wa_consent']?date('Y-m-d H:i:s'):null;
            $columns=array_keys($params);
            $sql='INSERT INTO tracer_responses ('.implode(',',$columns).',persetujuan_data,persetujuan_at) VALUES (:'.implode(',:',$columns).',1,NOW())';
            try{$statement=db()->prepare($sql);$statement->execute($params);}
            catch(PDOException $exception){
                if((int)($exception->errorInfo[1]??0)!==1062){throw $exception;}
                $find->execute(['key'=>$key]);$row=$find->fetch();
                if(!$row||!tracer_same_response($row,$data)){throw new InvalidArgumentException('Pengiriman ini sudah digunakan. Buka formulir baru atau hubungi BKK.');}$repeated=true;
            }
        }
        $digest=hash('sha256',json_encode($data,JSON_THROW_ON_ERROR));
        $additional=!$repeated&&($_SESSION['tracer_last_digest']??null)===$digest;
        $_SESSION['tracer_last_digest']=$digest;
        $_SESSION['tracer_receipt']=['expires'=>time()+1800,'repeated'=>$repeated,'additional'=>$additional];
        unset($_SESSION['tracer_form']);redirect('tracer-study/berhasil.php',303);
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
    catch(Throwable $exception){error_log('Simpan tracer gagal: '.get_class($exception).' '.$exception->getCode());$errors[]='Data belum dapat disimpan. Coba kembali dengan formulir yang sama; bila berlanjut, hubungi pengelola BKK.';}
}
$_SESSION['tracer_form']=['nonce'=>$nonce,'data'=>$data,'errors'=>$errors];redirect('tracer-study/formulir.php',303);
