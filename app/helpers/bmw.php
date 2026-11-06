<?php
declare(strict_types=1);
require_once __DIR__.'/panel.php';
require_once __DIR__.'/validation.php';

function bmw_description(): array
{
    $rows=panel_query("SELECT setting_key,setting_value FROM settings WHERE setting_key IN ('bmw_title','bmw_description','bmw_status','bmw_version')")->fetchAll(PDO::FETCH_KEY_PAIR);
    return ['judul'=>$rows['bmw_title']??'Program BMW Sakti','isi'=>$rows['bmw_description']??'','status'=>$rows['bmw_status']??'draft','version'=>(int)($rows['bmw_version']??0)];
}

function bmw_errors(array $data): array
{
    $errors=[];
    if(!valid_text($data['judul'],190)){$errors[]='Judul wajib diisi, maksimal 190 karakter.';}
    if(!valid_text($data['isi'],15000,$data['status']==='published')){$errors[]='Isi maksimal 15.000 karakter dan wajib diisi untuk publikasi.';}
    if(!in_array($data['status'],['draft','published'],true)){$errors[]='Status deskripsi tidak valid.';}return $errors;
}

function bmw_save(array $data,int $version): void
{
    if($errors=bmw_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    with_content_lock(static function()use($data,$version):void{
        db()->beginTransaction();
        try{
            $old=bmw_description();
            if($old['version']!==$version){throw new InvalidArgumentException('Deskripsi sudah diubah pengelola lain. Muat ulang halaman.');}
            foreach(['bmw_title'=>$data['judul'],'bmw_description'=>$data['isi'],'bmw_status'=>$data['status'],'bmw_version'=>(string)($version+1)] as $key=>$value){
                panel_query('INSERT INTO settings(setting_key,setting_value) VALUES(:key,:value) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',['key'=>$key,'value'=>$value]);
            }
            db()->commit();
        }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
    });
}
