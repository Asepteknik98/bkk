<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('zend.exception_ignore_args', '1');
ini_set('error_log', APP_ROOT . '/storage/logs/php-error.log');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $exception): void {
    // Pesan/trace driver dapat mengandung SQL atau kredensial.
    error_log('Kesalahan aplikasi: ' . get_class($exception) . '; kode=' . $exception->getCode()
        . '; lokasi=' . basename($exception->getFile()) . ':' . $exception->getLine());
    http_response_code(500);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Terjadi kesalahan aplikasi. Periksa storage/logs.\n");
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<h1>Aplikasi belum dapat memproses permintaan.</h1><p>Silakan hubungi pengelola.</p>';
    }
});

$GLOBALS['app_config'] = require __DIR__ . '/config/app.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/helpers/csrf.php';
date_default_timezone_set((string) app_config('timezone'));

if (PHP_SAPI !== 'cli') {
    start_app_session();
}
