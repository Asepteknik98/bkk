<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/vacancies.php';
$user=require_role('admin','staf_bkk');require_post_csrf();$id=positive_id($_POST['id']??null);if(!$id){panel_not_found();}
try{$deleted=with_content_lock(static fn():int=>panel_query('DELETE FROM lowongan WHERE id=:id',['id'=>$id])->rowCount());if(!$deleted){panel_not_found();}}
catch(PDOException $exception){if(($exception->errorInfo[1]??0)!==1451){throw $exception;}panel_message('Lowongan memiliki pencatatan alumni. Gunakan status Draft untuk mempertahankan riwayat.','panel/lowongan/');}
panel_message('Lowongan berhasil dihapus. Poster tetap disimpan di katalog media.','panel/lowongan/');
