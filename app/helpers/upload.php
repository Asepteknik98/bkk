<?php
declare(strict_types=1);
require_once __DIR__ . '/panel.php';
require_once __DIR__ . '/validation.php';

const IMAGE_MIMES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];

/** Tidak mempercayai nama, MIME, ukuran, atau temporary path dari input klien. */
function store_upload(?array $file, string $folder = 'berita', bool $allowPdf = false): ?array
{
    if ($file === null) { return null; }
    if (!isset($file['error']) || !is_int($file['error'])) {
        throw new InvalidArgumentException('Data unggahan tidak valid.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) { return null; }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Unggahan gagal atau melebihi batas server. Pilih berkas kembali.');
    }
    $name = $file['name'] ?? null;
    $temporary = $file['tmp_name'] ?? null;
    $fullPath = $file['full_path'] ?? $name;
    if (!is_string($name) || !is_string($temporary) || !is_uploaded_file($temporary)
        || !is_string($fullPath) || $fullPath !== $name
        || !valid_text($name, 255) || preg_match('~[\\\\/\x00-\x1f\x7f]~', $name)
        || preg_match('/\.(?:php\d*|phtml|pht|phar|cgi|pl|exe|com|bat|cmd|ps1|js|html?|shtml|svg)(?:\.|$)/i', $name)) {
        throw new InvalidArgumentException('Nama atau jenis berkas tidak diizinkan.');
    }
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = IMAGE_MIMES + ($allowPdf ? ['pdf' => 'application/pdf'] : []);
    if (!isset($allowed[$extension])) {
        throw new InvalidArgumentException('Gunakan JPG, PNG, WebP' . ($allowPdf ? ', atau PDF.' : '.'));
    }
    $size = filesize($temporary);
    $limit = $extension === 'pdf' ? 10 * 1024 * 1024 : 5 * 1024 * 1024;
    if ($size === false || $size < 1 || $size > $limit) {
        throw new InvalidArgumentException('Batas ukuran gambar 5 MB dan dokumen PDF 10 MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    if ($mime !== $allowed[$extension]) {
        throw new InvalidArgumentException('Isi berkas tidak sesuai ekstensi yang diizinkan.');
    }
    $width = $height = null;
    if ($extension !== 'pdf') {
        $image = @getimagesize($temporary);
        if (!$image || ($image['mime'] ?? '') !== $mime || $image[0] < 1 || $image[1] < 1
            || $image[0] > 10000 || $image[1] > 10000 || $image[0] * $image[1] > 40000000) {
            throw new InvalidArgumentException('Gambar rusak atau dimensinya melebihi batas.');
        }
        [$width, $height] = $image;
    }
    if ($extension === 'pdf') { $folder = 'dokumen'; }
    if (!in_array($folder, ['berita', 'lowongan', 'program', 'mitra', 'dokumen'], true)) {
        throw new InvalidArgumentException('Lokasi unggahan tidak diizinkan.');
    }
    $uploads = realpath(APP_ROOT . '/uploads');
    if (!$uploads) { throw new RuntimeException('Direktori unggahan tidak tersedia.'); }
    $directory = $uploads . DIRECTORY_SEPARATOR . $folder;
    if (!is_dir($directory) && !@mkdir($directory, 0755) && !is_dir($directory)) {
        throw new RuntimeException('Direktori unggahan tidak dapat dibuat.');
    }
    $realDirectory = realpath($directory);
    if (!$realDirectory || !str_starts_with($realDirectory, $uploads . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Lokasi unggahan tidak aman.');
    }
    $extension = $extension === 'jpeg' ? 'jpg' : $extension;
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file($temporary, $realDirectory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('Berkas tidak dapat disimpan.');
    }
    return ['nama_asli' => $name, 'path' => 'uploads/' . $folder . '/' . $filename,
        'mime_type' => $mime, 'ukuran_bytes' => $size, 'lebar' => $width, 'tinggi' => $height];
}

function register_media(array $upload, int $userId): int
{
    panel_query('INSERT INTO media (nama_asli, path, mime_type, ukuran_bytes, lebar, tinggi, uploaded_by) VALUES (:nama_asli, :path, :mime_type, :ukuran_bytes, :lebar, :tinggi, :uploaded_by)', $upload + ['uploaded_by' => $userId]);
    return (int) db()->lastInsertId();
}

/** Hanya berkas yang dikelola layanan ini, tanpa symlink keluar uploads. */
function managed_upload_path(string $path): ?string
{
    if (!preg_match('~^uploads/(?:berita|lowongan|program|mitra|dokumen)/[a-f0-9]{32}\.(?:jpg|png|webp|pdf)$~D', $path)) { return null; }
    $root = realpath(APP_ROOT . '/uploads');
    $real = realpath(APP_ROOT . '/' . $path);
    return $root && $real && is_file($real) && str_starts_with($real, $root . DIRECTORY_SEPARATOR) ? $real : null;
}

/** Hanya untuk berkas BARU yang belum tersimpan ke katalog/transaksi gagal. */
function discard_upload(?array $upload): void
{
    if ($upload) {
        try {
            // Setelah commit yang berhasil, berkas tidak boleh dibuang walau respons gagal.
            if (panel_query('SELECT id FROM media WHERE path=:path', ['path' => $upload['path']])->fetch()) { return; }
        } catch (Throwable) { error_log('Pembersihan unggahan ditunda karena status katalog belum dapat dipastikan.'); return; }
    }
    if ($upload && ($path = managed_upload_path($upload['path']))) {
        if (!unlink($path)) { error_log('Pembersihan unggahan baru gagal.'); }
    }
}

function media_is_image(array $media): bool
{
    return in_array($media['mime_type'], array_values(IMAGE_MIMES), true)
        && preg_match('/\.(?:jpg|png|webp)$/D', $media['path']) === 1
        && managed_upload_path($media['path']) !== null;
}

/** Periksa semua kolom konten relevan, termasuk draft dan teks yang memuat path. */
function media_reference_count(string $path): int
{
    $count = 0;
    $pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $path) . '%';
    foreach (['berita' => ['gambar', 'isi'], 'lowongan' => ['gambar', 'deskripsi'], 'program' => ['gambar', 'isi'], 'mitra' => ['logo', 'deskripsi']] as $table => [$image, $body]) {
        $count += (int) panel_query("SELECT COUNT(*) FROM $table WHERE $image = :path OR $body LIKE :pattern ESCAPE '!'", ['path' => $path, 'pattern' => $pattern])->fetchColumn();
    }
    return $count + (int) panel_query("SELECT COUNT(*) FROM settings WHERE setting_value LIKE :pattern ESCAPE '!'", ['pattern' => $pattern])->fetchColumn();
}
