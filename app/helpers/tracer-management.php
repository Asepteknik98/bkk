<?php
declare(strict_types=1);
require_once __DIR__.'/tracer-panel.php';

function tracer_record(int $id, bool $lock=false): array
{
    $row=panel_query('SELECT * FROM tracer_responses WHERE id=:id'.($lock?' FOR UPDATE':''),['id'=>$id])->fetch();
    if(!$row){throw new InvalidArgumentException('Data alumni tidak ditemukan.');}
    return $row;
}

function tracer_revision(array $row): string
{
    ksort($row);
    return hash('sha256',json_encode($row,JSON_THROW_ON_ERROR));
}

function tracer_check_revision(array $row,string $revision): void
{
    if(!hash_equals(tracer_revision($row),$revision)){
        throw new InvalidArgumentException('Data sudah berubah. Muat ulang sebelum melanjutkan.');
    }
}

function tracer_admin_update(int $id,array $input,string $revision): void
{
    db()->beginTransaction();
    try{
        $old=tracer_record($id,true);tracer_check_revision($old,$revision);
        $data=tracer_input($input);
        // Editing identity/activity never grants new data or WhatsApp consent.
        $data['persetujuan_data']=(bool)$old['persetujuan_data'];
        if($errors=tracer_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
        $params=[];foreach(tracer_fields() as $field=>$label){$params[$field]=$data[$field]===''?null:$data[$field];}
        $params['tahun_lulus']=(int)$data['tahun_lulus'];
        $set=implode(',',array_map(static fn($key)=>$key.'=:'.$key,array_keys($params)));
        panel_query('UPDATE tracer_responses SET '.$set.',updated_at=CURRENT_TIMESTAMP WHERE id=:id',$params+['id'=>$id]);
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function tracer_admin_delete(int $id,string $revision): void
{
    db()->beginTransaction();
    try{
        $row=tracer_record($id,true);tracer_check_revision($row,$revision);
        // Foreign keys restrict deleting alumni with application/work history.
        panel_query('DELETE FROM tracer_responses WHERE id=:id',['id'=>$id]);
        db()->commit();
    }catch(Throwable $exception){
        if(db()->inTransaction()){db()->rollBack();}
        if($exception instanceof PDOException&&(int)($exception->errorInfo[1]??0)===1451){
            throw new InvalidArgumentException('Data tidak dapat dihapus karena masih terkait riwayat lamaran atau pekerjaan alumni.');
        }
        throw $exception;
    }
}
