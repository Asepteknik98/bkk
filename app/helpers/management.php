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
    if ($current) { echo '<div class="current-image"><img src="'.e(content_image($current)).'" alt="'.e($label).' saat ini" width="120"><label class="check-label"><input type="checkbox" name="'.e($field).'_remove" value="1"> Lepaskan gambar saat ini</label></div>'; }
    echo '<label for="'.e($field).'_media">Atau pilih katalog (200 terbaru)</label><select id="'.e($field).'_media" name="'.e($field).'_media"><option value="">Pertahankan / tanpa gambar</option>';
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
    return ['manager_name'=>['Nama pengelola / penanggung jawab',150,false], 'manager_unit'=>['Unit pengelola',200,false], 'manager_address'=>['Alamat pengelola',2000,false], 'manager_email'=>['Email layanan privasi',191,false], 'manager_phone'=>['Telepon pengelola',50,false], 'copyright_owner'=>['Pemegang hak cipta konten sekolah',200,false], 'privacy_retention'=>['Keterangan masa simpan data',2000,false], 'site_name'=>['Nama BKK',150,true],'school_name'=>['Nama sekolah',150,true],
        'contact_address'=>['Alamat',2000,false],'contact_phone'=>['Nomor kontak',50,false],
        'contact_email'=>['Email kontak',191,false],'social_instagram'=>['Instagram (URL)',2048,false],
        'social_facebook'=>['Facebook (URL)',2048,false],'social_youtube'=>['YouTube (URL)',2048,false],
        'social_tiktok'=>['TikTok (URL)',2048,false],'social_x'=>['X (URL)',2048,false],
        'social_threads'=>['Threads (URL)',2048,false],'social_linkedin'=>['LinkedIn (URL)',2048,false],
        'social_telegram'=>['Telegram (URL)',2048,false],
        'profile_description'=>['Isi profil BKK (teks biasa)',50000,false],
        'participant_classes'=>['Pilihan kelas (satu per baris)',10000,false],
        'participant_departments'=>['Pilihan jurusan (satu per baris)',10000,false],
        'participant_industries'=>['Pilihan kelas industri (satu nama per baris)',10000,false],
        'participant_years'=>['Pilihan angkatan (tahun, satu per baris)',10000,false]];
}

/** Shared section allowlist for the settings navigation and save handler. */
function settings_sections(): array
{
    return [
        'identitas'=>['label'=>'Identitas & Kontak','icon'=>'identity','description'=>'Nama sekolah, identitas BKK, dan kontak layanan.','fields'=>['site_name','school_name','contact_phone','contact_email','contact_address']],
        'profil'=>['label'=>'Profil BKK','icon'=>'message','description'=>'Penjelasan BKK yang ditampilkan pada halaman profil publik.','fields'=>['profile_description']],
        'logo'=>['label'=>'Logo Website','icon'=>'media','description'=>'Logo BKK dan sekolah yang digunakan pada website.','fields'=>[]],
        'sosial'=>['label'=>'Media Sosial','icon'=>'home','description'=>'Tautan akun media sosial resmi. Kosongkan jika belum tersedia.','fields'=>array_keys(social_networks())],
        'privasi'=>['label'=>'Hak Cipta & Privasi','icon'=>'lock','description'=>'Penanggung jawab, layanan privasi, dan keterangan pengelolaan data.','fields'=>['manager_name','manager_unit','manager_address','manager_email','manager_phone','copyright_owner','privacy_retention']],
        'kelas'=>['label'=>'Pilihan Kelas','icon'=>'peserta','description'=>'Isi satu kelas per baris untuk pilihan pada formulir peserta. Nilai siswa lama tetap tersedia.','fields'=>['participant_classes']],
        'jurusan'=>['label'=>'Pilihan Jurusan','icon'=>'program','description'=>'Isi satu jurusan per baris untuk pilihan pada formulir peserta. Nilai siswa lama tetap tersedia.','fields'=>['participant_departments']],
        'kelas_industri'=>['label'=>'Pilihan Kelas Industri','icon'=>'lowongan','description'=>'Isi satu nama kelas industri per baris untuk dropdown siswa. Gunakan nama yang benar, bukan Ya/Tidak. Nilai siswa lama tetap tersimpan.','fields'=>['participant_industries']],
        'angkatan'=>['label'=>'Pilihan Angkatan','icon'=>'calendar','description'=>'Isi satu tahun per baris, antara 1900 dan 2199. Tahun pada data lama tetap tersedia.','fields'=>['participant_years']],
    ];
}
