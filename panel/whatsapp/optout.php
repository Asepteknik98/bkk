<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/whatsapp.php';
$user=require_role('admin','staf_bkk');require_post_csrf();
$type=$_POST['source']??'';$id=positive_id($_POST['id']??'');
if(!in_array($type,['pelamar','tracer'],true)||!$id){panel_not_found();}
$table=$type==='pelamar'?'lamaran':'tracer_responses';$column=$type==='pelamar'?'telepon':'nomor_telepon';
$raw=panel_query('SELECT '.$column.' FROM '.$table.' WHERE id=:id',['id'=>$id])->fetchColumn();
if($raw===false){panel_not_found();}$phone=wa_phone($raw);
db()->beginTransaction();
try{
    foreach(['lamaran'=>'telepon','tracer_responses'=>'nomor_telepon'] as $target=>$field){
        foreach(panel_query('SELECT id,'.$field.' FROM '.$target.'')->fetchAll() as $row){
            if($phone!==null&&wa_phone($row[$field])===$phone){panel_query('UPDATE '.$target.' SET wa_consent=0,wa_opted_out_at=NOW() WHERE id=:id',['id'=>(int)$row['id']]);}
        }
    }
    panel_query('UPDATE '.$table.' SET wa_consent=0,wa_opted_out_at=NOW() WHERE id=:id',['id'=>$id]);
    db()->commit();
}catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
panel_message('Persetujuan WhatsApp dinonaktifkan untuk nomor ini.','panel/'.($type==='pelamar'?'lamaran':'tracer-study').'/detail.php?id='.$id);
