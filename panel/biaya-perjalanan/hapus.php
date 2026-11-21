<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/pkl-schedules.php';
$user = require_role('admin', 'staf_bkk');
require_post_csrf();
pkl_schedules_require_ready();
$id = positive_id($_POST['id'] ?? null);
$version = positive_id($_POST['version'] ?? null);
if (!$id || !$version) { panel_not_found(); }
try {
    pkl_trip_delete($id, $version);
    $message = 'Catatan perjalanan dan biayanya berhasil dihapus. Penempatan serta jadwal monitoring tetap tersimpan.';
} catch (InvalidArgumentException $exception) {
    $message = $exception->getMessage();
} catch (Throwable $exception) {
    error_log('Hapus perjalanan gagal: ' . get_class($exception) . ' ' . $exception->getCode());
    $message = 'Perjalanan belum dapat dihapus. Muat ulang daftar dan coba kembali.';
}
panel_message($message, 'panel/biaya-perjalanan/');
