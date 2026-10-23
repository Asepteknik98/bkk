<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/learning-documents.php';
$user=require_role('admin','staf_bkk');
$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
$row=panel_query('SELECT * FROM learning_documents WHERE id=:id',['id'=>$id])->fetch();
$path=$row?learning_file_path($row['nama_simpan']):null;
if(!$path){panel_not_found();}
$handle=fopen($path,'rb');if(!$handle){panel_not_found();}
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="capaian-pembelajaran.pdf"; filename*=UTF-8\'\''.rawurlencode($row['nama_asli']));
header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');
header('Content-Length: '.fstat($handle)['size']);
fpassthru($handle);fclose($handle);
