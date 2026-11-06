<?php
declare(strict_types=1);
require_once __DIR__.'/partners.php';
require_once __DIR__.'/learning-documents.php';
require_once __DIR__.'/pkl.php';

function mou_ready(): bool
{
    return (bool)panel_query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mitra_mous'")->fetchColumn();
}

function mou_require_ready(): void
{
    if(!mou_ready()){http_response_code(503);exit('Impor database/updates/022_partner_profiles_mou.sql terlebih dahulu.');}
}

function mou_today(?DateTimeImmutable $now=null): string
{
    return ($now??new DateTimeImmutable('now'))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d');
}

function mou_status(string $start,string $end,?DateTimeImmutable $now=null): string
{
    $today=mou_today($now);return $today<$start?'Belum Berlaku':($today>$end?'Kedaluwarsa':'Aktif');
}

function mou_input(array $input): array
{
    $data=[];foreach(['previous_id','nomor_dokumen','tanggal_mulai','tanggal_berakhir'] as $key){$data[$key]=input_text($input,$key);}return $data;
}

function mou_errors(array $data): array
{
    $errors=[];if(!valid_text($data['nomor_dokumen'],190)){$errors[]='Nomor dokumen wajib diisi, maksimal 190 karakter.';}
    if(!pkl_date($data['tanggal_mulai'])||!pkl_date($data['tanggal_berakhir'])||$data['tanggal_berakhir']<$data['tanggal_mulai']){$errors[]='Tanggal MoU harus valid dan tanggal berakhir tidak sebelum tanggal mulai.';}
    if($data['previous_id']!==''&&!positive_id($data['previous_id'])){$errors[]='Riwayat MoU sebelumnya tidak valid.';}return $errors;
}

function mou_file_path(string $filename): ?string
{
    if(!preg_match('/^[a-f0-9]{64}\.pdf$/D',$filename)){return null;}
    $storage=realpath(APP_ROOT.'/storage');$directory=realpath(APP_ROOT.'/storage/mou');$path=realpath(APP_ROOT.'/storage/mou/'.$filename);
    return $storage&&$directory&&str_starts_with($directory,$storage.DIRECTORY_SEPARATOR)&&$path&&is_file($path)&&str_starts_with($path,$directory.DIRECTORY_SEPARATOR)?$path:null;
}

function mou_upload(?array $file): array
{
    if(!$file||($file['error']??null)!==UPLOAD_ERR_OK||!is_string($file['tmp_name']??null)||!is_string($file['name']??null)||!is_uploaded_file($file['tmp_name'])){
        throw new InvalidArgumentException('Berkas MoU PDF wajib diunggah, maksimal 5 MB.');
    }
    if(isset($file['full_path'])&&$file['full_path']!==$file['name']){throw new InvalidArgumentException('Lokasi unggahan tidak valid.');}
    $size=learning_validate_pdf($file['tmp_name'],$file['name']);
    $directory=APP_ROOT.'/storage/mou';if(!is_dir($directory)&&!mkdir($directory,0700,true)&&!is_dir($directory)){throw new RuntimeException('Penyimpanan MoU tidak tersedia.');}
    $root=realpath(APP_ROOT.'/storage');$real=realpath($directory);
    if(!$root||!$real||!str_starts_with($real,$root.DIRECTORY_SEPARATOR)){throw new RuntimeException('Lokasi penyimpanan MoU tidak valid.');}
    $filename=bin2hex(random_bytes(32)).'.pdf';if(!move_uploaded_file($file['tmp_name'],$real.DIRECTORY_SEPARATOR.$filename)){throw new RuntimeException('Berkas MoU gagal disimpan.');}
    return ['nama_asli'=>$file['name'],'nama_simpan'=>$filename,'ukuran'=>$size];
}

function mou_discard(?array $file): void
{
    if(!$file){return;}
    try{if(panel_query('SELECT id FROM mitra_mous WHERE nama_simpan=:file',['file'=>$file['nama_simpan']])->fetch()){return;}}
    catch(Throwable){error_log('Pembersihan MoU ditunda karena status database belum dapat dipastikan.');return;}
    $path=mou_file_path($file['nama_simpan']);if($path&&!unlink($path)){error_log('Pembersihan unggahan MoU baru tertunda.');}
}

function mou_save(int $partnerId,array $data,array $file,int $actor): int
{
    if($errors=mou_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    db()->beginTransaction();
    try{
        if(!panel_query('SELECT id FROM mitra WHERE id=:id FOR UPDATE',['id'=>$partnerId])->fetch()){throw new InvalidArgumentException('Mitra tidak ditemukan.');}
        if($data['previous_id']!==''){
            $old=panel_query('SELECT * FROM mitra_mous WHERE id=:id AND mitra_id=:partner FOR UPDATE',['id'=>(int)$data['previous_id'],'partner'=>$partnerId])->fetch();
            if(!$old){throw new InvalidArgumentException('MoU sebelumnya harus milik mitra yang sama.');}
            if($data['tanggal_berakhir']<=$old['tanggal_berakhir']){throw new InvalidArgumentException('Tanggal berakhir perpanjangan harus setelah tanggal berakhir MoU sebelumnya.');}
        }
        $data['previous_id']=$data['previous_id']===''?null:(int)$data['previous_id'];
        panel_query('INSERT INTO mitra_mous(mitra_id,previous_id,nomor_dokumen,tanggal_mulai,tanggal_berakhir,nama_asli,nama_simpan,ukuran,uploaded_by) VALUES(:partner,:previous_id,:nomor_dokumen,:tanggal_mulai,:tanggal_berakhir,:nama_asli,:nama_simpan,:ukuran,:actor)',$data+$file+['partner'=>$partnerId,'actor'=>$actor]);
        $id=(int)db()->lastInsertId();db()->commit();return $id;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}
