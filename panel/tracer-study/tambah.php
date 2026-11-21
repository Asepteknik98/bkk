<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');
$data=tracer_input([]);$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_post_csrf();
    $data=tracer_input($_POST);
    $errors=tracer_errors($data);
    $nonce=is_string($_POST['submission_token']??null)?$_POST['submission_token']:'';
    $issued=$_SESSION['admin_tracer_forms'][$nonce]??0;
    if (!$issued || $issued<time()-7200) {$errors[]='Formulir kedaluwarsa. Muat ulang halaman.';}
    if (!$errors) {
        try {
            $key=hash('sha256','admin-tracer:'.$user['id'].':'.$nonce);
            $existing=panel_query('SELECT * FROM tracer_responses WHERE submission_key=:key LIMIT 1',['key'=>$key])->fetch();
            if ($existing && !tracer_same_response($existing,$data)) {throw new InvalidArgumentException('Formulir sudah digunakan. Buka formulir baru untuk data lain.');}
            if (!$existing) {
                $params=[];
                foreach(tracer_fields() as $field=>$label){$params[$field]=$data[$field]===''?null:$data[$field];}
                $params['tahun_lulus']=(int)$data['tahun_lulus'];
                $params['submission_key']=$key;
                $params['versi_persetujuan']='admin-v1:'.$user['id'];
                $columns=array_keys($params);
                try {
                    panel_query('INSERT INTO tracer_responses ('.implode(',',$columns).',persetujuan_data,persetujuan_at) VALUES (:'.implode(',:',$columns).',1,NOW())',$params);
                } catch (PDOException $exception) {
                    if ((int)($exception->errorInfo[1]??0)!==1062) {throw $exception;}
                    $existing=panel_query('SELECT * FROM tracer_responses WHERE submission_key=:key LIMIT 1',['key'=>$key])->fetch();
                    if (!$existing || !tracer_same_response($existing,$data)) {throw $exception;}
                }
            }
            panel_message('Data alumni berhasil disimpan dan masuk ke rekap Tracer Study.','panel/tracer-study/');
        } catch (InvalidArgumentException $exception) {$errors[]=$exception->getMessage();}
        catch (Throwable $exception) {error_log('Admin tracer gagal: '.get_class($exception).' '.$exception->getCode());$errors[]='Data belum dapat disimpan. Silakan coba kembali.';}
    }
} else {
    $nonce=bin2hex(random_bytes(32));
    foreach(($_SESSION['admin_tracer_forms']??[]) as $token=>$issued){if($issued<time()-7200){unset($_SESSION['admin_tracer_forms'][$token]);}}
    $_SESSION['admin_tracer_forms'][$nonce]=time();
}
$editing=false;require __DIR__.'/form.php';
