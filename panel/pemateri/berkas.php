<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/speakers.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');$kind=input_text($_GET,'jenis');if(!$id||!in_array($kind,['cv','foto'],true)){panel_not_found();}
$row=panel_query('SELECT * FROM speakers WHERE id=:id',['id'=>$id])->fetch();$path=$row&&!empty($row[$kind.'_file'])?speaker_path($row[$kind.'_file']):null;if(!$path){panel_not_found();}
$mime=$kind==='cv'?'application/pdf':$row['foto_mime'];if(!in_array($mime,['application/pdf','image/jpeg','image/png','image/webp'],true)){panel_not_found();}
$handle=fopen($path,'rb');if(!$handle){panel_not_found();}
header('Content-Type: '.$mime);header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');
if($kind==='cv'){header('Content-Disposition: attachment; filename="cv-pemateri.pdf"; filename*=UTF-8\'\''.rawurlencode($row['cv_nama']));}else{header('Content-Disposition: inline');}
header('Content-Length: '.fstat($handle)['size']);fpassthru($handle);fclose($handle);
