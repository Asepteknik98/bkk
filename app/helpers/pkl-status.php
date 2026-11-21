<?php
declare(strict_types=1);

function pkl_status_ready(): bool
{
    static $ready;
    return $ready??=(bool)panel_query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pkl_status_history'")->fetchColumn();
}

function pkl_status_options(bool $legacy=false): array
{
    $options=['aktif'=>'Sedang PKL','belum'=>'Belum PKL','gagal'=>'Gagal PKL','selesai'=>'PKL selesai'];
    if($legacy){$options['arsip']='Arsip lama (perlu ditinjau)';}
    return $options;
}

function pkl_status_label(string $status): string
{
    return pkl_status_options(true)[$status]??'Status tidak dikenal';
}

function pkl_status_reason(string $reason,bool $required): void
{
    if(!valid_text($reason,2000,$required)){
        throw new InvalidArgumentException('Alasan perubahan status wajib diisi, maksimal 2.000 karakter.');
    }
}

/** Called inside the placement transaction; history is never edited or deleted. */
function pkl_record_status(int $id,?string $before,string $after,string $reason,int $version,int $actor): void
{
    if(!pkl_status_ready()||$before===$after){return;}
    pkl_status_reason($reason,$before!==null||$after==='gagal');
    panel_query('INSERT INTO pkl_status_history(placement_id,placement_version,status_sebelum,status_sesudah,alasan,sumber,changed_by,recorded_at) VALUES(:id,:version,:before,:after,:reason,:source,:actor,:time)',[
        'id'=>$id,'version'=>$version,'before'=>$before,'after'=>$after,
        'reason'=>$reason!==''?$reason:'Penempatan baru dicatat.',
        'source'=>$before===null?'baru':'perubahan','actor'=>$actor,'time'=>date('Y-m-d H:i:s'),
    ]);
}

function pkl_change_status(int $studentId,int $placementId,int $version,string $status,string $reason,int $actor): void
{
    if(!pkl_status_ready()){throw new InvalidArgumentException('Impor pembaruan 027_pkl_status_history.sql terlebih dahulu.');}
    if(!isset(pkl_status_options()[$status])){throw new InvalidArgumentException('Pilih salah satu dari empat status PKL.');}
    pkl_status_reason($reason,true);
    db()->beginTransaction();
    try{
        if(!panel_query('SELECT id FROM siswa WHERE id=:id FOR UPDATE',['id'=>$studentId])->fetchColumn()){throw new InvalidArgumentException('Siswa tidak ditemukan.');}
        $latest=(int)panel_query('SELECT id FROM pkl_placements WHERE siswa_id=:id ORDER BY id DESC LIMIT 1',['id'=>$studentId])->fetchColumn();
        $old=pkl_find($placementId,true);
        if((int)$old['siswa_id']!==$studentId||$latest!==$placementId||(int)$old['version']!==$version){throw new InvalidArgumentException('Penempatan sudah berubah. Muat ulang halaman sebelum memperbarui status.');}
        if($old['status']===$status){throw new InvalidArgumentException('Status masih sama; tidak ada perubahan yang disimpan.');}
        panel_query('UPDATE pkl_placements SET status=:status,version=version+1,updated_by=:actor WHERE id=:id',['status'=>$status,'actor'=>$actor,'id'=>$placementId]);
        pkl_record_status($placementId,$old['status'],$status,$reason,$version+1,$actor);
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}
