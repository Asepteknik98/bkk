<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/employment.php';
$user = require_role('admin', 'staf_bkk');
require_post_csrf();
employment_require_ready();
$id = positive_id($_POST['id'] ?? null);
$version = positive_id($_POST['version'] ?? null);
if (!$id || !$version) { panel_not_found(); }
try {
    employment_type_delete($id, $version);
    $message = 'Jenis hubungan kerja berhasil dihapus.';
} catch (InvalidArgumentException $exception) {
    $message = $exception->getMessage();
} catch (Throwable $exception) {
    error_log('Hapus jenis hubungan kerja gagal: ' . get_class($exception) . ' ' . $exception->getCode());
    $message = 'Jenis belum dapat dihapus. Data mungkin masih digunakan atau sedang diproses. Muat ulang daftar dan coba kembali.';
}
panel_message($message, 'panel/hubungan-kerja/');
