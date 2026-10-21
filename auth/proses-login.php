<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once APP_ROOT . '/app/helpers/login-throttle.php';
require_post_csrf();

$email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$retry = max(login_attempt('ip:' . $ip, 30), login_attempt('account-ip:' . $ip . ':' . $email, 5));
if ($retry > 0) {
    http_response_code(429);
    header('Retry-After: ' . $retry);
    exit('Terlalu banyak percobaan login. Silakan coba kembali setelah 15 menit.');
}

$validInput = strlen($email) <= 191 && filter_var($email, FILTER_VALIDATE_EMAIL)
    && strlen($password) >= 1 && strlen($password) <= 72;
$user = false;
if ($validInput) {
    $statement = db()->prepare('SELECT id, password_hash, role, aktif FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
}
// Hash pembanding bukan akun/kredensial login; samakan pekerjaan untuk email yang tidak ditemukan.
$dummyHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
$verified = password_verify($validInput ? $password : '', $user ? $user['password_hash'] : $dummyHash);
unset($password, $_POST['password']);
if (!$validInput || !$user || !$verified || !(int) $user['aktif']
    || !in_array($user['role'], ['admin', 'staf_bkk'], true)) {
    $_SESSION['login_error'] = 'Email atau password salah, atau akun tidak dapat digunakan.';
    redirect('auth/login.php', 303);
}

$statement = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
$statement->execute(['id' => $user['id']]);
session_regenerate_id(true);
$_SESSION = ['user_id' => (int) $user['id']];
csrf_token();
redirect('panel/index.php', 303);
