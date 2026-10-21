<?php
declare(strict_types=1);
require_once __DIR__.'/upload.php';
require_once __DIR__.'/public.php';

function management_image(string $field, ?string $old, int $userId, ?array $upload): ?string
{
    if ($upload) { register_media($upload,$userId); return $upload['path']; }
    $selection=input_text($_POST,$field.'_media');
    if ($selection!=='') {
        $id=positive_id($selection);
        $media=$id?panel_query('SELECT * FROM media WHERE id=:id FOR UPDATE',['id'=>$id])->fetch():false;
        if (!$media || !media_is_image($media)) { throw new InvalidArgumentException('Pilihan logo katalog tidak valid.'); }
        return $media['path'];
    }
    return ($_POST[$field.'_remove']??'')==='1'?null:$old;
}

function management_image_field(string $field,string $label,?string $current): void
{
    $images=panel_query("SELECT * FROM media WHERE mime_type IN ('image/jpeg','image/png','image/webp') ORDER BY id DESC LIMIT 200")->fetchAll();
    echo '<label for="'.e($field).'">'.e($label).'</label><input id="'.e($field).'" type="file" name="'.e($field).'" accept=".jpg,.jpeg,.png,.webp"><small>JPG/PNG/WebP maksimal 5 MB. Unggahan baru mengalahkan katalog. Berkas lama tetap di katalog media.</small>';
    if ($current) { echo '<div class="current-image"><img src="'.e(content_image($current)).'" alt="Logo saat ini" width="120"><label class="check-label"><input type="checkbox" name="'.e($field).'_remove" value="1"> Lepaskan logo saat ini</label></div>'; }
    echo '<label for="'.e($field).'_media">Atau pilih katalog (200 terbaru)</label><select id="'.e($field).'_media" name="'.e($field).'_media"><option value="">Pertahankan / tanpa logo</option>';
    foreach ($images as $image) { if (media_is_image($image)) { echo '<option value="'.$image['id'].'">'.e($image['nama_asli']).' (#'.$image['id'].')</option>'; } }
    echo '</select>';
}

function management_upload(string $field): ?array
{
    if (isset($_FILES[$field])&&!is_array($_FILES[$field])) { throw new InvalidArgumentException('Data unggahan tidak valid.'); }
    return store_upload($_FILES[$field]??null,'mitra');
}

function management_transaction(callable $operation): mixed
{
    return with_content_lock(static function() use($operation) {
        db()->beginTransaction();
        try { $result=$operation(); db()->commit(); return $result; }
        catch (Throwable $exception) { if(db()->inTransaction()){db()->rollBack();} throw $exception; }
    });
}

function management_error(Throwable $exception,string $path): never
{
    if($exception instanceof InvalidArgumentException){$message=$exception->getMessage();}
    else{error_log('Pengelolaan gagal: '.get_class($exception).' '.$exception->getCode());$message='Data belum tersimpan. Periksa database/unggahan dan coba kembali.';}
    panel_message($message,$path);
}

function settings_fields(): array
{
    return ['site_name'=>['Nama BKK',150,true],'school_name'=>['Nama sekolah',150,true],
        'contact_address'=>['Alamat',2000,false],'contact_phone'=>['Nomor kontak',50,false],
        'contact_email'=>['Email kontak',191,false],'social_instagram'=>['Instagram (URL)',2048,false],
        'social_facebook'=>['Facebook (URL)',2048,false],'social_youtube'=>['YouTube (URL)',2048,false],
        'profile_description'=>['Isi profil BKK (teks biasa)',50000,false]];
}
