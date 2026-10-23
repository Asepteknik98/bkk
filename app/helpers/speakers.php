<?php
declare(strict_types=1);
require_once __DIR__.'/learning-documents.php';

function speaker_filters(array $input): array
{
    [$filters]=student_filters(array_intersect_key($input,array_flip(['q','kelas_industri'])));unset($filters['urut'],$filters['angkatan']);
    $eventId=positive_id($input['event_id']??'');
    if(isset($input['event_id'])&&$input['event_id']!==''&&!$eventId){throw new InvalidArgumentException('Filter kegiatan tidak valid.');}
    if($eventId){$filters['event_id']=(string)$eventId;}
    $where='1=1';$params=[];
    if($filters['q']!==''){$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$filters['q']).'%';$where.=" AND (s.nama LIKE :nama ESCAPE '!' OR s.instansi LIKE :instansi ESCAPE '!' OR s.keahlian LIKE :keahlian ESCAPE '!')";$params+=['nama'=>$pattern,'instansi'=>$pattern,'keahlian'=>$pattern];}
    if($filters['kelas_industri']!==''){$where.=' AND (s.kelas_industri=:industri OR EXISTS(SELECT 1 FROM speaker_events se JOIN attendance_events e ON e.id=se.event_id WHERE se.speaker_id=s.id AND e.kelas_industri=:event_industri))';$params+=['industri'=>$filters['kelas_industri'],'event_industri'=>$filters['kelas_industri']];}
    if($eventId){$where.=' AND EXISTS(SELECT 1 FROM speaker_events se WHERE se.speaker_id=s.id AND se.event_id=:event)';$params['event']=$eventId;}
    return [$filters,$where,$params];
}

function speaker_input(array $input): array
{
    $data=[];foreach(['nama','instansi','keahlian','kelas_industri','bio'] as $key){$data[$key]=input_text($input,$key);}return $data;
}

function speaker_errors(array $data): array
{
    $errors=[];foreach(['nama'=>150,'instansi'=>190,'keahlian'=>190,'kelas_industri'=>150,'bio'=>5000] as $key=>$max){if(!valid_text($data[$key],$max,$key==='nama')){$errors[]=ucfirst(str_replace('_',' ',$key)).' tidak valid (maksimal '.$max.' karakter).';}}return $errors;
}

function speaker_event_ids(mixed $input): array
{
    if(!is_array($input)||count($input)>100){throw new InvalidArgumentException('Pilih maksimal 100 kegiatan.');}
    $ids=[];foreach($input as $value){$id=positive_id($value);if(!$id){throw new InvalidArgumentException('Pilihan kegiatan tidak valid.');}$ids[$id]=$id;}return array_values($ids);
}

function speaker_path(string $filename): ?string
{
    if(!preg_match('/^[a-f0-9]{64}\.(?:pdf|jpg|png|webp)$/D',$filename)){return null;}
    $directory=realpath(APP_ROOT.'/storage/speakers');$path=realpath(APP_ROOT.'/storage/speakers/'.$filename);
    return $directory&&$path&&is_file($path)&&str_starts_with($path,$directory.DIRECTORY_SEPARATOR)?$path:null;
}

function speaker_remove_file(?string $filename): void
{
    $path=$filename?speaker_path($filename):null;if($path&&!unlink($path)){error_log('Pembersihan berkas pemateri tertunda.');}
}

/** All assets stay private; photos are decoded and re-encoded before serving. */
function speaker_upload(?array $file,string $kind): ?array
{
    if(!in_array($kind,['cv','foto'],true)){throw new InvalidArgumentException('Jenis berkas tidak valid.');}
    if($file===null||($file['error']??null)===UPLOAD_ERR_NO_FILE){return null;}
    if(($file['error']??null)!==UPLOAD_ERR_OK||!is_string($file['tmp_name']??null)||!is_string($file['name']??null)||!is_uploaded_file($file['tmp_name'])){throw new InvalidArgumentException('Unggahan '.$kind.' gagal. Pilih berkas kembali.');}
    $tmp=$file['tmp_name'];$name=$file['name'];$mime='application/pdf';$extension='pdf';$image=null;
    if($kind==='cv'){$size=learning_validate_pdf($tmp,$name);}
    else{
        if(!valid_text($name,200)||preg_match('~[\\\\/\x00-\x1f\x7f]~',$name)){throw new InvalidArgumentException('Nama foto tidak valid.');}
        $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];$size=filesize($tmp);$mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if($size===false||$size<1||$size>3*1024*1024||!isset($types[$mime])){throw new InvalidArgumentException('Foto harus JPG, PNG, atau WebP maksimal 3 MB.');}
        $extension=$types[$mime];$ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
        $info=@getimagesize($tmp);
        if(!in_array($ext,$mime==='image/jpeg'?['jpg','jpeg']:[$extension],true)||!$info||$info['mime']!==$mime||$info[0]<1||$info[1]<1||$info[0]>6000||$info[1]>6000||$info[0]*$info[1]>16000000){throw new InvalidArgumentException('Foto rusak, ekstensi tidak sesuai, atau dimensi terlalu besar.');}
        $image=@imagecreatefromstring(file_get_contents($tmp));if(!$image){throw new InvalidArgumentException('Foto tidak dapat dibaca.');}
    }
    $directory=APP_ROOT.'/storage/speakers';$filename=bin2hex(random_bytes(32)).'.'.$extension;
    try{
        if(!is_dir($directory)&&!mkdir($directory,0700,true)&&!is_dir($directory)){throw new RuntimeException('Penyimpanan pemateri tidak tersedia.');}
        $storage=realpath(APP_ROOT.'/storage');$real=realpath($directory);if(!$storage||!$real||!str_starts_with($real,$storage.DIRECTORY_SEPARATOR)){throw new RuntimeException('Lokasi penyimpanan tidak valid.');}
        $destination=$real.DIRECTORY_SEPARATOR.$filename;
        if($kind==='cv'){$ok=move_uploaded_file($tmp,$destination);}
        else{imagesavealpha($image,true);$ok=match($extension){'jpg'=>imagejpeg($image,$destination,90),'png'=>imagepng($image,$destination,6),'webp'=>imagewebp($image,$destination,90)};}
        if(!$ok){throw new RuntimeException('Berkas gagal disimpan.');}
        $size=filesize($destination);if($size===false||$size<1){throw new RuntimeException('Berkas gagal disimpan.');}
        return ['file'=>$filename,'name'=>$name,'mime'=>$mime,'size'=>$size];
    }catch(Throwable $exception){speaker_remove_file($filename);throw $exception;}
    finally{if($image){imagedestroy($image);}}
}

function speaker_save(array $data,?int $id,?int $version,int $userId,array $files,array $remove,array $eventIds): int
{
    if($errors=speaker_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    $old=null;$garbage=[];db()->beginTransaction();
    try{
        if($id!==null){$old=panel_query('SELECT * FROM speakers WHERE id=:id FOR UPDATE',['id'=>$id])->fetch();if(!$old){throw new InvalidArgumentException('Pemateri sudah tidak tersedia.');}if((int)$old['version']!==$version){throw new InvalidArgumentException('Profil sudah diubah pengelola lain. Muat ulang sebelum menyimpan.');}}
        foreach($eventIds as $eventId){if(!panel_query('SELECT id FROM attendance_events WHERE id=:id LOCK IN SHARE MODE',['id'=>$eventId])->fetch()){throw new InvalidArgumentException('Kegiatan yang dipilih sudah tidak tersedia.');}}
        $params=$data;
        foreach(['cv'=>['cv_nama','cv_file','cv_size'],'foto'=>['foto_file','foto_mime','foto_size']] as $kind=>$columns){
            foreach($columns as $column){$params[$column]=!empty($remove[$kind])?null:($old[$column]??null);}
            if(isset($files[$kind])){$file=$files[$kind];$params[$kind.'_file']=$file['file'];$params[$kind.'_size']=$file['size'];if($kind==='cv'){$params['cv_nama']=$file['name'];}else{$params['foto_mime']=$file['mime'];}}
            if($old&&$old[$kind.'_file']!==$params[$kind.'_file']){$garbage[]=$old[$kind.'_file'];}
        }
        $columns=array_keys($params);
        if($id!==null){panel_query('UPDATE speakers SET '.implode(',',array_map(static fn($key)=>"$key=:$key",$columns)).',version=version+1 WHERE id=:id',$params+['id'=>$id]);}
        else{panel_query('INSERT INTO speakers('.implode(',',$columns).',created_by) VALUES(:'.implode(',:',$columns).',:created_by)',$params+['created_by'=>$userId]);$id=(int)db()->lastInsertId();}
        panel_query('DELETE FROM speaker_events WHERE speaker_id=:id',['id'=>$id]);foreach($eventIds as $eventId){panel_query('INSERT INTO speaker_events(speaker_id,event_id) VALUES(:speaker,:event)',['speaker'=>$id,'event'=>$eventId]);}
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
    foreach($garbage as $filename){speaker_remove_file($filename);}return $id;
}

function speaker_delete(int $id,int $version): void
{
    db()->beginTransaction();
    try{$row=panel_query('SELECT * FROM speakers WHERE id=:id FOR UPDATE',['id'=>$id])->fetch();if(!$row){throw new InvalidArgumentException('Pemateri sudah tidak tersedia.');}if((int)$row['version']!==$version){throw new InvalidArgumentException('Profil sudah berubah. Muat ulang halaman sebelum menghapus.');}panel_query('DELETE FROM speakers WHERE id=:id',['id'=>$id]);db()->commit();}
    catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
    speaker_remove_file($row['cv_file']);speaker_remove_file($row['foto_file']);
}
