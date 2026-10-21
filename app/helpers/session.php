<?php
declare(strict_types=1);

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $path = parse_url(base_url(), PHP_URL_PATH) ?: '/';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $directory = APP_ROOT . '/storage/sessions';
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Penyimpanan session tidak tersedia.');
    }
    session_save_path($directory);
    session_name('bkk_session');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => $path,
        'secure' => $https, 'httponly' => true, 'samesite' => 'Lax',
    ]);
    if (!session_start()) {
        throw new RuntimeException('Session tidak dapat dimulai.');
    }
    header('Cache-Control: no-store');
}

function destroy_app_session(): void
{
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000, 'path' => $params['path'],
        'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_destroy();
}
