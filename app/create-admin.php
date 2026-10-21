<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Skrip ini hanya dapat dijalankan melalui CLI.');
}
require_once __DIR__ . '/bootstrap.php';

function ask(string $label): string
{
    fwrite(STDOUT, $label);
    $value = fgets(STDIN);
    if ($value === false) {
        throw new RuntimeException('Input dibatalkan.');
    }
    return trim($value);
}

/** Password dibaca tanpa echo, tanpa argumen proses atau variabel lingkungan. */
function secret(string $label): string
{
    fwrite(STDOUT, $label);
    if (PHP_OS_FAMILY === 'Windows') {
        $command = [
            'powershell.exe', '-NoProfile', '-Command',
            '$s = Read-Host -AsSecureString; $p = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s); try { [Console]::Out.Write([Runtime.InteropServices.Marshal]::PtrToStringBSTR($p)) } finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($p) }',
        ];
        $process = proc_open($command, [0 => STDIN, 1 => ['pipe', 'w'], 2 => STDERR], $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('Pembacaan password aman tidak tersedia.');
        }
        $value = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $status = proc_close($process);
        fwrite(STDOUT, PHP_EOL);
        if ($status !== 0 || $value === false) {
            throw new RuntimeException('Password gagal dibaca. Gunakan terminal interaktif.');
        }
        return $value;
    }
    if (!function_exists('stream_isatty') || !stream_isatty(STDIN)) {
        throw new RuntimeException('Gunakan terminal interaktif.');
    }
    exec('stty -echo', $output, $status);
    if ($status !== 0) {
        throw new RuntimeException('Pembacaan password aman tidak tersedia.');
    }
    try {
        $value = fgets(STDIN);
    } finally {
        exec('stty echo');
        fwrite(STDOUT, PHP_EOL);
    }
    if ($value === false) {
        throw new RuntimeException('Input dibatalkan.');
    }
    return rtrim($value, "\r\n");
}

$pdo = null;
$locked = false;
try {
    $pdo = db();
    $locked = (int) $pdo->query("SELECT GET_LOCK('bkk_create_first_admin', 0)")->fetchColumn() === 1;
    if (!$locked) {
        throw new RuntimeException('Pembuatan admin sedang berjalan di terminal lain.');
    }
    if ((int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0) {
        throw new RuntimeException('Admin sudah tersedia. Skrip hanya untuk admin pertama.');
    }
    $nama = ask('Nama admin: ');
    $email = strtolower(ask('Email admin: '));
    if ($nama === '' || preg_match('//u', $nama) !== 1 || strlen($nama) > 150
        || strlen($email) > 191 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Nama atau email tidak valid (nama maksimal 150 byte).');
    }
    $password = secret('Password (12–72 byte): ');
    $confirmation = secret('Ulangi password: ');
    if (strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")
        || !hash_equals($password, $confirmation)) {
        throw new RuntimeException('Password harus 12–72 byte, tanpa karakter NUL, dan konfirmasi harus sama.');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    unset($password, $confirmation);
    $statement = $pdo->prepare("INSERT INTO users (nama, email, password_hash, role, aktif) VALUES (:nama, :email, :hash, 'admin', 1)");
    $statement->execute(['nama' => $nama, 'email' => $email, 'hash' => $hash]);
    fwrite(STDOUT, "Admin pertama berhasil dibuat. Silakan masuk melalui auth/login.php.\n");
} catch (PDOException $exception) {
    // Jangan mencetak/log exception SQL yang mungkin berisi input sensitif.
    fwrite(STDERR, "Gagal membuat admin. Periksa konfigurasi database, impor tabel, dan keunikan email.\n");
    $failed = true;
} catch (RuntimeException $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    $failed = true;
} finally {
    unset($password, $confirmation, $hash);
    if ($locked && $pdo instanceof PDO) {
        $pdo->query("SELECT RELEASE_LOCK('bkk_create_first_admin')");
    }
}
exit(isset($failed) ? 1 : 0);
