<?php
declare(strict_types=1);
require_once __DIR__.'/validation.php';

function application_file_fields(): array
{
    return ['cv'=>['CV / daftar riwayat hidup',true], 'ijazah'=>['Ijazah / surat keterangan lulus (SKL)',true], 'sertifikat'=>['Sertifikat',false], 'pendukung'=>['Berkas pendukung lainnya',false]];
}

/** Validasi seluruh berkas sebelum memindahkan apa pun. */
function validate_application_files(array $files): array
{
    $validated = []; $total = 0;
    $types = ['application/pdf'=>'pdf', 'image/jpeg'=>'jpg', 'image/png'=>'png'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach (application_file_fields() as $field=>[$label,$required]) {
        $file = $files[$field] ?? null;
        if ($file === null || (is_array($file) && ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE)) {
            if ($required) { throw new InvalidArgumentException("Berkas $label wajib diunggah."); }
            continue;
        }
        if (!is_array($file) || !is_int($file['error'] ?? null) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException("Unggahan $label gagal. Pilih berkas maksimal 2 MB.");
        }
        $tmp = $file['tmp_name'] ?? null;
        if (!is_string($tmp) || !is_uploaded_file($tmp)) { throw new InvalidArgumentException("Unggahan $label tidak valid."); }
        $size = filesize($tmp);
        if ($size === false || $size < 1 || $size > 2*1024*1024) { throw new InvalidArgumentException("Berkas $label maksimal 2 MB dan tidak boleh kosong."); }
        $mime = $finfo->file($tmp);
        $name = $file['name'] ?? null;
        if (!is_string($name) || !valid_text($name,200) || preg_match('/[\x00-\x1f\x7f]/', $name)) { throw new InvalidArgumentException("Nama berkas $label tidak valid."); }
        $extension = strtolower(pathinfo($name,PATHINFO_EXTENSION));
        if (!isset($types[$mime]) || !in_array($extension, $mime==='image/jpeg' ? ['jpg','jpeg'] : [$types[$mime]], true)) { throw new InvalidArgumentException("Berkas $label harus berupa PDF, JPG, atau PNG."); }
        if ($mime === 'application/pdf') {
            $handle = fopen($tmp,'rb'); $signature = $handle ? fread($handle,5) : ''; if ($handle) { fclose($handle); }
            if ($signature !== '%PDF-') { throw new InvalidArgumentException("Berkas PDF $label tidak valid."); }
        } elseif (@getimagesize($tmp) === false) { throw new InvalidArgumentException("Berkas gambar $label tidak valid."); }
        $total += $size;
        $validated[] = ['jenis'=>$field,'nama_asli'=>basename(str_replace('\\','/',$name)),'tmp_name'=>$tmp,'mime_type'=>$mime,'ukuran'=>$size,'nama_simpan'=>bin2hex(random_bytes(24)).'.'.$types[$mime]];
    }
    if ($total > 6*1024*1024) { throw new InvalidArgumentException('Total seluruh berkas maksimal 6 MB.'); }
    return $validated;
}

function application_file_directory(): string
{
    return APP_ROOT.'/storage/application-files';
}

function save_application_files(PDO $pdo, int $applicationId, array $files, array &$moved): void
{
    $directory = application_file_directory();
    if (!is_dir($directory) && !mkdir($directory,0700,true) && !is_dir($directory)) { throw new RuntimeException('Penyimpanan berkas tidak tersedia.'); }
    $insert = $pdo->prepare('INSERT INTO lamaran_berkas (lamaran_id,jenis,nama_asli,nama_simpan,mime_type,ukuran) VALUES (:lamaran_id,:jenis,:nama_asli,:nama_simpan,:mime_type,:ukuran)');
    foreach ($files as $file) {
        $path = $directory.'/'.$file['nama_simpan'];
        if (!move_uploaded_file($file['tmp_name'],$path)) { throw new RuntimeException('Penyimpanan berkas gagal.'); }
        $moved[] = $path;
        $insert->execute(['lamaran_id'=>$applicationId]+array_intersect_key($file,array_flip(['jenis','nama_asli','nama_simpan','mime_type','ukuran'])));
    }
}
