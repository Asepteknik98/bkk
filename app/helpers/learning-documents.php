<?php
declare(strict_types=1);
require_once __DIR__.'/students.php';

function learning_filters(array $input): array
{
    [$filters,$where,$params]=student_filters($input);unset($filters['urut']);
    $where=str_replace(['nama LIKE :nama','nis LIKE :nis'],['judul LIKE :nama','deskripsi LIKE :nis'],$where);
    return [$filters,$where,$params];
}

function learning_input(array $input): array
{
    $data=[];
    foreach(['judul','deskripsi','angkatan','kelas_industri'] as $key){$data[$key]=input_text($input,$key);}
    return $data;
}

function learning_errors(array $data): array
{
    $errors=[];
    foreach(['judul'=>190,'deskripsi'=>5000,'kelas_industri'=>150] as $key=>$max){
        if(!valid_text($data[$key],$max,$key!=='deskripsi')){$errors[]=ucfirst(str_replace('_',' ',$key)).' tidak valid (maksimal '.$max.' karakter).';}
    }
    if(!preg_match('/^(19|20|21)[0-9]{2}$/D',$data['angkatan'])){$errors[]='Angkatan harus berupa tahun masuk (1900–2199).';}
    return $errors;
}

function learning_file_path(string $filename): ?string
{
    if(!preg_match('/^[a-f0-9]{64}\.pdf$/D',$filename)){return null;}
    $directory=realpath(APP_ROOT.'/storage/learning-documents');
    $path=realpath(APP_ROOT.'/storage/learning-documents/'.$filename);
    return $directory&&$path&&is_file($path)&&str_starts_with($path,$directory.DIRECTORY_SEPARATOR)?$path:null;
}

function learning_remove_file(string $filename): void
{
    $path=learning_file_path($filename);
    if($path&&!unlink($path)){error_log('Pembersihan berkas capaian pembelajaran tertunda.');}
}

function learning_validate_pdf(string $path,string $name): int
{
    if(!valid_text($name,200)||preg_match('~[\\\\/\x00-\x1f\x7f]~',$name)||strtolower(pathinfo($name,PATHINFO_EXTENSION))!=='pdf'){
        throw new InvalidArgumentException('Nama berkas harus berupa nama PDF, maksimal 200 karakter.');
    }
    $size=filesize($path);
    if($size===false||$size<1||$size>5*1024*1024){throw new InvalidArgumentException('Dokumen PDF maksimal 5 MB dan tidak boleh kosong.');}
    $handle=fopen($path,'rb');$signature=$handle?fread($handle,5):'';if($handle){fclose($handle);}
    if($signature!=='%PDF-'||(new finfo(FILEINFO_MIME_TYPE))->file($path)!=='application/pdf'){
        throw new InvalidArgumentException('Isi berkas harus berupa dokumen PDF.');
    }
    return $size;
}

function learning_upload(?array $file): ?array
{
    if($file===null||($file['error']??null)===UPLOAD_ERR_NO_FILE){return null;}
    if(($file['error']??null)!==UPLOAD_ERR_OK||!is_string($file['tmp_name']??null)||!is_string($file['name']??null)||!is_uploaded_file($file['tmp_name'])){
        throw new InvalidArgumentException('Unggahan gagal. Pilih PDF maksimal 5 MB.');
    }
    $size=learning_validate_pdf($file['tmp_name'],$file['name']);
    $directory=APP_ROOT.'/storage/learning-documents';
    if(!is_dir($directory)&&!mkdir($directory,0700,true)&&!is_dir($directory)){throw new RuntimeException('Penyimpanan dokumen tidak tersedia.');}
    $storage=realpath(APP_ROOT.'/storage');$realDirectory=realpath($directory);
    if(!$storage||!$realDirectory||!str_starts_with($realDirectory,$storage.DIRECTORY_SEPARATOR)){throw new RuntimeException('Lokasi penyimpanan tidak valid.');}
    $filename=bin2hex(random_bytes(32)).'.pdf';
    if(!move_uploaded_file($file['tmp_name'],$realDirectory.DIRECTORY_SEPARATOR.$filename)){throw new RuntimeException('Dokumen gagal disimpan.');}
    return ['nama_asli'=>$file['name'],'nama_simpan'=>$filename,'ukuran'=>$size];
}

function learning_save(array $data,?int $id,int $userId,?array $file): int
{
    if($errors=learning_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    $old=null;
    db()->beginTransaction();
    try{
        if($id!==null){$old=panel_query('SELECT * FROM learning_documents WHERE id=:id FOR UPDATE',['id'=>$id])->fetch();if(!$old){throw new InvalidArgumentException('Dokumen sudah tidak tersedia.');}}
        if(!$old&&!$file){throw new InvalidArgumentException('Dokumen PDF wajib diunggah.');}
        $params=$data+($file?:array_intersect_key($old,array_flip(['nama_asli','nama_simpan','ukuran'])));
        if($id!==null){panel_query('UPDATE learning_documents SET judul=:judul,deskripsi=:deskripsi,angkatan=:angkatan,kelas_industri=:kelas_industri,nama_asli=:nama_asli,nama_simpan=:nama_simpan,ukuran=:ukuran WHERE id=:id',$params+['id'=>$id]);}
        else{panel_query('INSERT INTO learning_documents(judul,deskripsi,angkatan,kelas_industri,nama_asli,nama_simpan,ukuran,uploaded_by) VALUES(:judul,:deskripsi,:angkatan,:kelas_industri,:nama_asli,:nama_simpan,:ukuran,:uploaded_by)',$params+['uploaded_by'=>$userId]);$id=(int)db()->lastInsertId();}
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
    if($file&&$old&&$old['nama_simpan']!==$file['nama_simpan']){learning_remove_file($old['nama_simpan']);}
    return $id;
}

function learning_delete(int $id): void
{
    db()->beginTransaction();
    try{
        $row=panel_query('SELECT * FROM learning_documents WHERE id=:id FOR UPDATE',['id'=>$id])->fetch();
        if(!$row){throw new InvalidArgumentException('Dokumen sudah tidak tersedia.');}
        panel_query('DELETE FROM learning_documents WHERE id=:id',['id'=>$id]);db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
    learning_remove_file($row['nama_simpan']);
}
