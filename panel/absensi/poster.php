<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/event-resources.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
$row=panel_query('SELECT poster_file,poster_mime FROM event_resources WHERE event_id=:id',['id'=>$id])->fetch();$path=$row&&$row['poster_file']?event_poster_path($row['poster_file']):null;if(!$path||!in_array($row['poster_mime'],['image/jpeg','image/png','image/webp'],true)){panel_not_found();}
$handle=fopen($path,'rb');if(!$handle){panel_not_found();}header('Content-Type: '.$row['poster_mime']);header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');header('Content-Length: '.fstat($handle)['size']);fpassthru($handle);fclose($handle);
