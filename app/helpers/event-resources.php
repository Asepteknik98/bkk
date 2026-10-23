<?php
declare(strict_types=1);
require_once __DIR__.'/attendance.php';
require_once __DIR__.'/jobs.php';

function event_resources_defaults(): array
{
    return ['pretest_url'=>'','pretest_enabled'=>0,'poster_file'=>null,'poster_name'=>null,'poster_mime'=>null,'poster_size'=>null,'poster_enabled'=>0,'version'=>0];
}

function event_poster_path(string $filename): ?string
{
    if(!preg_match('/^[a-f0-9]{64}\.(?:jpg|png|webp)$/D',$filename)){return null;}
    $directory=realpath(APP_ROOT.'/storage/event-posters');$path=realpath(APP_ROOT.'/storage/event-posters/'.$filename);
    return $directory&&$path&&is_file($path)&&str_starts_with($path,$directory.DIRECTORY_SEPARATOR)?$path:null;
}

function event_poster_remove(?string $filename): void
{
    $path=$filename?event_poster_path($filename):null;if($path&&!unlink($path)){error_log('Pembersihan poster kegiatan tertunda.');}
}

function event_poster_upload(?array $file): ?array
{
    if($file===null||($file['error']??null)===UPLOAD_ERR_NO_FILE){return null;}
    if(($file['error']??null)!==UPLOAD_ERR_OK||!is_string($file['tmp_name']??null)||!is_string($file['name']??null)||!is_uploaded_file($file['tmp_name'])){throw new InvalidArgumentException('Unggahan poster gagal. Pilih berkas kembali.');}
    $tmp=$file['tmp_name'];$name=$file['name'];
    if(!valid_text($name,200)||preg_match('~[\\\\/\x00-\x1f\x7f]~',$name)){throw new InvalidArgumentException('Nama poster tidak valid.');}
    $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];$size=filesize($tmp);$mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if($size===false||$size<1||$size>3*1024*1024||!isset($types[$mime])){throw new InvalidArgumentException('Poster harus JPG, PNG, atau WebP maksimal 3 MB.');}
    $extension=$types[$mime];$ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));$info=@getimagesize($tmp);
    if(!in_array($ext,$mime==='image/jpeg'?['jpg','jpeg']:[$extension],true)||!$info||$info['mime']!==$mime||$info[0]<1||$info[1]<1||$info[0]>6000||$info[1]>6000||$info[0]*$info[1]>16000000){throw new InvalidArgumentException('Poster rusak, ekstensi tidak sesuai, atau dimensinya terlalu besar.');}
    $image=@imagecreatefromstring(file_get_contents($tmp));if(!$image){throw new InvalidArgumentException('Poster tidak dapat dibaca.');}
    $filename=bin2hex(random_bytes(32)).'.'.$extension;$directory=APP_ROOT.'/storage/event-posters';
    try{
        if(!is_dir($directory)&&!mkdir($directory,0700,true)&&!is_dir($directory)){throw new RuntimeException('Penyimpanan poster tidak tersedia.');}
        $storage=realpath(APP_ROOT.'/storage');$real=realpath($directory);if(!$storage||!$real||!str_starts_with($real,$storage.DIRECTORY_SEPARATOR)){throw new RuntimeException('Lokasi poster tidak valid.');}
        $destination=$real.DIRECTORY_SEPARATOR.$filename;imagesavealpha($image,true);
        $ok=match($extension){'jpg'=>imagejpeg($image,$destination,90),'png'=>imagepng($image,$destination,6),'webp'=>imagewebp($image,$destination,90)};
        if(!$ok||!is_file($destination)){throw new RuntimeException('Poster gagal disimpan.');}
        return ['poster_file'=>$filename,'poster_name'=>$name,'poster_mime'=>$mime,'poster_size'=>filesize($destination)];
    }catch(Throwable $exception){event_poster_remove($filename);throw $exception;}
    finally{imagedestroy($image);}
}

function event_resources_save(int $id,int $version,array $input,?array $poster,int $userId): void
{
    $url=input_text($input,'pretest_url');
    if($url!==''&&!http_url($url)){throw new InvalidArgumentException('Link pretest harus berupa URL http/https yang valid, maksimal 2.048 karakter.');}
    $pretest=($input['pretest_enabled']??'')==='1';$posterEnabled=($input['poster_enabled']??'')==='1';$remove=($input['hapus_poster']??'')==='1';
    if($pretest&&$url===''){throw new InvalidArgumentException('Isi link pretest sebelum mengaktifkannya.');}
    db()->beginTransaction();
    try{
        if(!panel_query('SELECT id FROM attendance_events WHERE id=:id FOR UPDATE',['id'=>$id])->fetch()){throw new InvalidArgumentException('Kegiatan tidak tersedia.');}
        $existing=panel_query('SELECT * FROM event_resources WHERE event_id=:id FOR UPDATE',['id'=>$id])->fetch();$old=$existing?:event_resources_defaults();
        if((int)$old['version']!==$version){throw new InvalidArgumentException('Pengaturan sudah diubah pengelola lain. Muat ulang sebelum menyimpan.');}
        $file=array_intersect_key($old,array_flip(['poster_file','poster_name','poster_mime','poster_size']));
        if($remove){$file=array_fill_keys(array_keys($file),null);if(!$poster){$posterEnabled=false;}}
        if($poster){$file=$poster;}
        if($posterEnabled&&(!$file['poster_file']||!event_poster_path($file['poster_file']))){throw new InvalidArgumentException('Upload poster sebelum mengaktifkannya.');}
        $params=['event_id'=>$id,'pretest_url'=>$url,'pretest_enabled'=>$pretest?1:0,'poster_enabled'=>$posterEnabled?1:0,'updated_by'=>$userId,'version'=>$version+1]+$file;
        if($existing){panel_query('UPDATE event_resources SET pretest_url=:pretest_url,pretest_enabled=:pretest_enabled,poster_enabled=:poster_enabled,poster_file=:poster_file,poster_name=:poster_name,poster_mime=:poster_mime,poster_size=:poster_size,updated_by=:updated_by,version=:version WHERE event_id=:event_id',$params);}
        else{panel_query('INSERT INTO event_resources(event_id,pretest_url,pretest_enabled,poster_enabled,poster_file,poster_name,poster_mime,poster_size,updated_by,version) VALUES(:event_id,:pretest_url,:pretest_enabled,:poster_enabled,:poster_file,:poster_name,:poster_mime,:poster_size,:updated_by,:version)',$params);}
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
    if($old['poster_file']!==$file['poster_file']){event_poster_remove($old['poster_file']);}
}
