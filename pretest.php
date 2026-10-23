<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/public.php';
require_once __DIR__.'/app/helpers/event-resources.php';
header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');
$id=positive_id($_GET['id']??'');
$row=$id?(public_query('SELECT pretest_url FROM event_resources WHERE event_id=:id AND pretest_enabled=1',['id'=>$id])[0]??null):null;
if(!$row||!http_url($row['pretest_url'])){http_response_code(404);require APP_ROOT.'/404.php';exit;}
header('Location: '.$row['pretest_url'],true,302);exit;
