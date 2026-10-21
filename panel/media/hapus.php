<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/news.php';
$user = require_role('admin', 'staf_bkk');
require_post_csrf();
$id = positive_id($_POST['id'] ?? null);
if (!$id) { panel_not_found(); }
try {
    $found = with_content_lock(static function () use ($id): bool {
        db()->beginTransaction();
        $path = $trash = null;
        try {
            $media = panel_query('SELECT * FROM media WHERE id=:id FOR UPDATE', ['id' => $id])->fetch();
            if (!$media) { db()->rollBack(); return false; }
            if (media_reference_count($media['path']) > 0) {
                throw new InvalidArgumentException('Berkas masih digunakan konten lain atau pengaturan. Lepaskan semua pemakaian sebelum menghapusnya.');
            }
            $path = managed_upload_path($media['path']);
            if (!$path) { throw new InvalidArgumentException('Berkas tidak tersedia atau path tidak aman. Penghapusan dibatalkan.'); }
            $directory = APP_ROOT . '/storage/media-trash';
            if (!is_dir($directory) && !@mkdir($directory, 0700) && !is_dir($directory)) { throw new RuntimeException('Direktori sementara tidak tersedia.'); }
            $trash = $directory . '/' . bin2hex(random_bytes(16)) . '.bin';
            if (!rename($path, $trash)) { $trash = null; throw new RuntimeException('Berkas tidak dapat dipindahkan.'); }
            panel_query('DELETE FROM media WHERE id=:id', ['id' => $id]);
            db()->commit();
        } catch (Throwable $exception) {
            if (db()->inTransaction()) { db()->rollBack(); }
            if ($trash && $path && !rename($trash, $path)) { error_log('Pemulihan berkas media gagal; periksa storage/media-trash.'); }
            throw $exception;
        }
        // Setelah commit, berkas tidak lagi publik; bila unlink gagal, tetap terlindung di storage.
        if ($trash && !unlink($trash)) { error_log('Pembersihan media-trash tertunda.'); }
        return true;
    });
    if (!$found) { panel_not_found(); }
    $message = 'Berkas berhasil dihapus dari katalog media.';
} catch (InvalidArgumentException $exception) { $message = $exception->getMessage(); }
catch (Throwable $exception) {
    error_log('Hapus media gagal: ' . get_class($exception) . ' ' . $exception->getCode());
    $message = 'Media belum dapat dihapus. Periksa izin direktori dan koneksi database.';
}
panel_message($message, 'panel/media/');
