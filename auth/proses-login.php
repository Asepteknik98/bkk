<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once APP_ROOT . '/app/helpers/login-throttle.php';
require_once APP_ROOT . '/app/middleware/auth.php';
require_post_csrf();

$email = is_string($_POST['username'] ?? $_POST['email'] ?? null) ? strtolower(trim($_POST['username'] ?? $_POST['email'])) : '';
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$retry = max(login_attempt('ip:' . $ip, 30), login_attempt('account-ip:' . $ip . ':' . $email, 5));
if ($retry > 0) {
    http_response_code(429);
    header('Retry-After: ' . $retry);
    exit('Terlalu banyak percobaan login. Silakan coba kembali setelah 15 menit.');
}

$validInput = strlen($email) <= 191 && (str_contains($email, '@') ? filter_var($email, FILTER_VALIDATE_EMAIL) : preg_match('/^[a-z0-9][a-z0-9._-]{2,49}$/D', $email))
    && strlen($password) >= 1 && strlen($password) <= 72;
$user = false;
if ($validInput) {
    $column = str_contains($email, '@') ? 'email' : 'username';
    $statement = db()->prepare('SELECT id, password_hash, role, aktif, session_version FROM users WHERE ' . $column . ' = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
}
// Hash pembanding bukan akun/kredensial login; samakan pekerjaan untuk email yang tidak ditemukan.
$dummyHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
$verified = password_verify($validInput ? $password : '', $user ? $user['password_hash'] : $dummyHash);
unset($password, $_POST['password']);
if (!$validInput || !$user || !$verified || !(int) $user['aktif']
    || !in_array($user['role'], ['admin', 'staf_bkk', 'siswa'], true)) {
    $_SESSION['login_error'] = 'Username/email atau password salah, atau akun tidak dapat digunakan.';
    redirect('auth/login.php', 303);
}

if($user['role']==='siswa'){
    $mapping=db()->prepare('SELECT sa.siswa_id FROM student_accounts sa JOIN siswa s ON s.id=sa.siswa_id WHERE sa.user_id=:id');
    $mapping->execute(['id'=>$user['id']]);
    if(!$mapping->fetchColumn()){$_SESSION['login_error']='Akun siswa belum terhubung ke Data Siswa. Hubungi pengelola.';redirect('auth/login.php',303);}
}

$statement = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
$statement->execute(['id' => $user['id']]);
session_regenerate_id(true);
$_SESSION = ['user_id' => (int) $user['id'], 'auth_version' => (int) $user['session_version']];
csrf_token();
redirect(auth_home($user), 303);
