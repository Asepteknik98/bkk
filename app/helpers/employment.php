<?php
declare(strict_types=1);
require_once __DIR__.'/panel.php';
require_once __DIR__.'/jobs.php';

function employment_ready(): bool
{
    return (int)panel_query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('employment_types','vacancy_details','alumni_application_results','alumni_work_profiles')")->fetchColumn()===4;
}

function employment_require_ready(): void
{
    if(!employment_ready()){http_response_code(503);exit('Impor database/updates/024_jobs_alumni_outcomes.sql terlebih dahulu.');}
}

function employment_options(?int $selected=null): array
{
    return panel_query('SELECT * FROM employment_types WHERE aktif=1 OR id=:selected ORDER BY nama,id',['selected'=>$selected??0])->fetchAll();
}

function employment_validate_type(int $id,?int $old=null): array
{
    $type=panel_query('SELECT * FROM employment_types WHERE id=:id LOCK IN SHARE MODE',['id'=>$id])->fetch();
    if(!$type||(!(int)$type['aktif']&&$id!==$old)){throw new InvalidArgumentException('Pilih jenis hubungan kerja aktif yang tersedia.');}return $type;
}

function employment_type_errors(array $data): array
{
    $errors=[];
    if(!valid_text($data['nama'],100)){$errors[]='Nama wajib diisi, maksimal 100 karakter.';}
    if(!valid_text($data['penjelasan'],10000,false)){$errors[]='Penjelasan maksimal 10.000 karakter.';}
    if($data['rujukan_url']!==''&&!http_url($data['rujukan_url'])){$errors[]='Tautan rujukan harus URL http/https yang valid.';}
    if(!in_array($data['aktif'],['0','1'],true)){$errors[]='Status tidak valid.';}return $errors;
}

function employment_type_save(array $data,?int $id,int $version,int $actor): void
{
    if($errors=employment_type_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    db()->beginTransaction();
    try{
        if($id){
            $old=panel_query('SELECT version FROM employment_types WHERE id=:id FOR UPDATE',['id'=>$id])->fetchColumn();
            if(!$old||(int)$old!==$version){throw new InvalidArgumentException('Jenis hubungan kerja sudah berubah. Muat ulang halaman.');}
            panel_query('UPDATE employment_types SET nama=:nama,penjelasan=:penjelasan,rujukan_url=:rujukan_url,aktif=:aktif,version=version+1,updated_by=:actor WHERE id=:id',$data+['actor'=>$actor,'id'=>$id]);
        }else{
            $code='jenis_'.bin2hex(random_bytes(8));
            panel_query('INSERT INTO employment_types(kode,nama,penjelasan,rujukan_url,aktif,updated_by) VALUES(:kode,:nama,:penjelasan,:rujukan_url,:aktif,:actor)',$data+['kode'=>$code,'actor'=>$actor]);
        }
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}
