<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/student-portal.php';
require_once APP_ROOT.'/app/helpers/event-resources.php';
[$user,$student]=require_student();$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}student_event($student,$id);
$row=panel_query('SELECT pretest_url FROM event_resources WHERE event_id=:id AND pretest_enabled=1',['id'=>$id])->fetch();if(!$row||!http_url($row['pretest_url'])){panel_not_found();}
header('Cache-Control: private, no-store');header('Referrer-Policy: no-referrer');header('Location: '.$row['pretest_url'],true,302);exit;
