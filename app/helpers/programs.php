<?php
declare(strict_types=1);
require_once __DIR__ . '/panel.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/public.php';

function program_input(array $input): array
{
    $data=[];
    foreach(['kategori','judul','slug','ringkasan','isi','tautan_pendaftaran','status','media_id'] as $key){$data[$key]=input_text($input,$key);}
    $data['hapus_gambar']=($input['hapus_gambar']??'')==='1';
    return $data;
}

function program_errors(array $data): array
{
    $errors=[];
    if(!array_key_exists($data['kategori'],program_categories())){$errors[]='Kategori program tidak diizinkan.';}
    foreach(['judul'=>[255,true],'slug'=>[191,false],'ringkasan'=>[1000,true],'isi'=>[100000,true]] as $key=>[$max,$required]){
        if(!valid_text($data[$key],$max,$required)){$errors[]=ucfirst($key)." harus berupa teks valid, maksimal $max karakter".($required?' dan wajib diisi.':'.');}
    }
    if($data['tautan_pendaftaran']!==''&&!http_url($data['tautan_pendaftaran'])){$errors[]='Tautan pendaftaran harus berupa URL http/https valid atau dikosongkan.';}
    if(!in_array($data['status'],['draft','published'],true)){$errors[]='Status program tidak valid.';}
    if($data['media_id']!==''&&positive_id($data['media_id'])===null){$errors[]='Pilihan gambar tidak valid.';}
    return $errors;
}

function save_program(array $data,?int $id,int $userId,?array $upload):int
{
    if($errors=program_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    return with_content_lock(static function()use($data,$id,$userId,$upload):int{
        db()->beginTransaction();
        try{
            $old=$id?panel_query('SELECT * FROM program WHERE id=:id FOR UPDATE',['id'=>$id])->fetch():null;
            if($id&&!$old){throw new InvalidArgumentException('Program sudah tidak tersedia.');}
            $image=$data['hapus_gambar']?null:($old['gambar']??null);
            if($upload){register_media($upload,$userId);$image=$upload['path'];}
            elseif($data['media_id']!==''){
                $media=panel_query('SELECT * FROM media WHERE id=:id FOR UPDATE',['id'=>(int)$data['media_id']])->fetch();
                if(!$media||!media_is_image($media)){throw new InvalidArgumentException('Gambar katalog tidak tersedia atau bukan gambar yang valid.');}
                $image=$media['path'];
            }
            $base=slugify($data['slug']!==''?$data['slug']:$data['judul']);$slug=$base;
            for($suffix=2;panel_query('SELECT id FROM program WHERE slug=:slug AND id<>:id LIMIT 1',['slug'=>$slug,'id'=>$id??0])->fetch();$suffix++){$slug=$base.'-'.$suffix;}
            $params=array_intersect_key($data,array_flip(['kategori','judul','ringkasan','isi','status']));
            $params+=['slug'=>$slug,'gambar'=>$image,'tautan_pendaftaran'=>$data['tautan_pendaftaran']?:null];
            if($id){panel_query('UPDATE program SET kategori=:kategori,judul=:judul,slug=:slug,ringkasan=:ringkasan,isi=:isi,gambar=:gambar,tautan_pendaftaran=:tautan_pendaftaran,status=:status WHERE id=:id',$params+['id'=>$id]);}
            else{panel_query('INSERT INTO program(kategori,judul,slug,ringkasan,isi,gambar,tautan_pendaftaran,status,penulis_id) VALUES(:kategori,:judul,:slug,:ringkasan,:isi,:gambar,:tautan_pendaftaran,:status,:penulis_id)',$params+['penulis_id'=>$userId]);$id=(int)db()->lastInsertId();}
            db()->commit();return $id;
        }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
    });
}
