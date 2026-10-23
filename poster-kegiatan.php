<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/public.php';
require_once __DIR__.'/app/helpers/event-resources.php';
header('Cache-Control: no-store');
$id=positive_id($_GET['id']??'');
$row=$id?(public_query('SELECT poster_file,poster_mime FROM event_resources WHERE event_id=:id AND poster_enabled=1',['id'=>$id])[0]??null):null;
$path=$row&&$row['poster_file']?event_poster_path($row['poster_file']):null;
if(!$path||!in_array($row['poster_mime'],['image/jpeg','image/png','image/webp'],true)){http_response_code(404);require APP_ROOT.'/404.php';exit;}
$handle=fopen($path,'rb');if(!$handle){http_response_code(404);require APP_ROOT.'/404.php';exit;}
header('Content-Type: '.$row['poster_mime']);header('Content-Disposition: inline');header('X-Content-Type-Options: nosniff');header('Content-Length: '.fstat($handle)['size']);fpassthru($handle);fclose($handle);
