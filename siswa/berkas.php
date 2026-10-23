<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/student-portal.php';
require_once APP_ROOT.'/app/helpers/learning-documents.php';
require_once APP_ROOT.'/app/helpers/speakers.php';
require_once APP_ROOT.'/app/helpers/event-resources.php';
[$user,$student]=require_student();$id=positive_id($_GET['id']??'');$kind=input_text($_GET,'jenis');if(!$id){panel_not_found();}
$path=null;$mime='';$name='';$attachment=false;
if($kind==='capaian'){$row=student_document($student,$id);$path=learning_file_path($row['nama_simpan']);$mime='application/pdf';$name=$row['nama_asli'];$attachment=true;}
elseif(in_array($kind,['cv','foto'],true)){$row=student_speaker($student,$id);$path=$row[$kind.'_file']?speaker_path($row[$kind.'_file']):null;$mime=$kind==='cv'?'application/pdf':($row['foto_mime']??'');$name=$row['cv_nama']??'cv.pdf';$attachment=$kind==='cv';}
elseif($kind==='poster'){student_event($student,$id);$row=panel_query('SELECT poster_file,poster_mime FROM event_resources WHERE event_id=:id AND poster_enabled=1',['id'=>$id])->fetch();$path=$row&&$row['poster_file']?event_poster_path($row['poster_file']):null;$mime=$row['poster_mime']??'';}
if(!$path||!in_array($mime,['application/pdf','image/jpeg','image/png','image/webp'],true)){panel_not_found();}
$handle=fopen($path,'rb');if(!$handle){panel_not_found();}header('Content-Type: '.$mime);header('Cache-Control: private, no-store');header('X-Content-Type-Options: nosniff');
header($attachment?'Content-Disposition: attachment; filename="dokumen.pdf"; filename*=UTF-8\'\''.rawurlencode($name):'Content-Disposition: inline');header('Content-Length: '.fstat($handle)['size']);fpassthru($handle);fclose($handle);
