<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/news.php';
$user = require_role('admin', 'staf_bkk');
require_post_csrf();
$upload = null;
try {
    if (isset($_FILES['berkas']) && !is_array($_FILES['berkas'])) { throw new InvalidArgumentException('Data unggahan tidak valid.'); }
    $upload = store_upload($_FILES['berkas'] ?? null, 'berita', true);
    if (!$upload) { throw new InvalidArgumentException('Pilih berkas yang akan diunggah.'); }
    with_content_lock(static function () use ($upload, $user): void {
        db()->beginTransaction();
        try { register_media($upload, (int) $user['id']); db()->commit(); }
        catch (Throwable $exception) { if (db()->inTransaction()) { db()->rollBack(); } throw $exception; }
    });
    panel_message('Berkas berhasil diunggah ke katalog media.', 'panel/media/');
} catch (InvalidArgumentException $exception) {
    $message = $exception->getMessage();
} catch (Throwable $exception) {
    error_log('Unggah media gagal: ' . get_class($exception) . ' ' . $exception->getCode());
    $message = 'Unggahan belum dapat disimpan. Periksa izin direktori unggahan dan koneksi database.';
}
discard_upload($upload);
panel_message($message, 'panel/media/');
