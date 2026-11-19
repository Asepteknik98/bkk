<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/panel.php';
$user = require_role('admin', 'staf_bkk');
require_post_csrf();
require_once APP_ROOT . '/app/helpers/careers.php';
require_once APP_ROOT . '/app/helpers/application-files.php';
$id = positive_id($_POST['id'] ?? null);
if (!$id) { panel_not_found(); }
$paths = [];
try {
    db()->beginTransaction();
    $row = panel_query('SELECT id FROM lamaran WHERE id=:id FOR UPDATE', ['id'=>$id])->fetch();
    if (!$row) { throw new InvalidArgumentException('Lamaran sudah dihapus atau tidak tersedia.'); }
    if (career_application_links_ready() && panel_query('SELECT lamaran_id FROM alumni_application_results WHERE lamaran_id=:id', ['id'=>$id])->fetchColumn()) {
        throw new InvalidArgumentException('Lamaran terkait riwayat hasil alumni tidak dapat dihapus agar riwayat tetap tersimpan.');
    }
    $files = panel_query('SELECT nama_simpan FROM lamaran_berkas WHERE lamaran_id=:id', ['id'=>$id])->fetchAll();
    $root = realpath(application_file_directory());
    foreach ($files as $file) {
        $name = $file['nama_simpan'];
        if (!is_string($name) || !preg_match('/^[a-f0-9]{48}\.(?:pdf|jpg|png)$/D', $name)) {
            throw new InvalidArgumentException('Referensi berkas tidak valid. Penghapusan dibatalkan; hubungi administrator.');
        }
        // Shared files, if present, must stay available to other applications.
        if ((int)panel_query('SELECT COUNT(*) FROM lamaran_berkas WHERE nama_simpan=:nama AND lamaran_id<>:id', ['nama'=>$name,'id'=>$id])->fetchColumn() > 0) { continue; }
        if (!$root) { continue; }
        $candidate = $root . DIRECTORY_SEPARATOR . $name;
        if (is_link($candidate)) { throw new InvalidArgumentException('Lokasi berkas tidak aman. Penghapusan dibatalkan.'); }
        $path = realpath($candidate);
        if ($path && is_file($path) && dirname($path) === $root) { $paths[] = $path; }
    }
    panel_query('DELETE FROM lamaran WHERE id=:id', ['id'=>$id]);
    db()->commit();
} catch (Throwable $exception) {
    if (db()->inTransaction()) { db()->rollBack(); }
    if ($exception instanceof InvalidArgumentException) { $message = $exception->getMessage(); }
    else {
        error_log('Hapus lamaran gagal: ' . get_class($exception) . ' ' . $exception->getCode());
        $message = 'Lamaran belum dapat dihapus. Muat ulang daftar dan coba kembali.';
    }
    panel_message($message, 'panel/lamaran/');
}
// Remove private attachments only after the database deletion has committed.
foreach (array_unique($paths) as $path) {
    if (is_file($path) && !unlink($path)) { error_log('Pembersihan berkas lamaran tertunda setelah penghapusan.'); }
}
panel_message('Lamaran berhasil dihapus.', 'panel/lamaran/');
