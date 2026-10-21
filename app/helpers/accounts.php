<?php
declare(strict_types=1);
require_once __DIR__ . '/panel.php';
require_once __DIR__ . '/validation.php';

function account_password(string $password, string $confirmation): string
{
    if (strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0") || !hash_equals($password, $confirmation)) {
        throw new InvalidArgumentException('Password harus 12–72 byte, tanpa NUL, dan konfirmasi harus sama.');
    }
    return password_hash($password, PASSWORD_DEFAULT);
}

/** Satu lock untuk semua perubahan akun; periksa ulang kewenangan di dalam transaksi. */
function account_change(int $actorId, bool $adminOnly, callable $operation): mixed
{
    $name = 'bkk_users_' . substr(hash('sha256', app_config('database')['name']), 0, 32);
    if ((int) panel_query('SELECT GET_LOCK(:name, 5)', ['name' => $name])->fetchColumn() !== 1) {
        throw new InvalidArgumentException('Akun sedang diperbarui. Silakan coba lagi.');
    }
    try {
        db()->beginTransaction();
        $actor = panel_query('SELECT * FROM users WHERE id=:id FOR UPDATE', ['id' => $actorId])->fetch();
        if (!$actor || !(int) $actor['aktif'] || (int) $actor['session_version'] !== (int) ($_SESSION['auth_version'] ?? -1)
            || ($adminOnly && $actor['role'] !== 'admin')) {
            throw new InvalidArgumentException('Sesi atau hak akses berubah. Silakan login kembali.');
        }
        $result = $operation($actor);
        db()->commit();
        return $result;
    } catch (Throwable $exception) {
        if (db()->inTransaction()) { db()->rollBack(); }
        throw $exception;
    } finally { panel_query('SELECT RELEASE_LOCK(:name)', ['name' => $name]); }
}

function account_identity(array $input, ?int $id): array
{
    $data = ['nama' => input_text($input, 'nama'), 'username' => strtolower(input_text($input, 'username')), 'email' => strtolower(input_text($input, 'email'))];
    if (!valid_text($data['nama'], 150) || !preg_match('/^[a-z0-9][a-z0-9._-]{2,49}$/D', $data['username'])
        || strlen($data['email']) > 191 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Nama wajib maksimal 150 karakter, username 3–50 huruf kecil/angka/titik/garis (diawali huruf/angka), dan email harus valid.');
    }
    if (panel_query('SELECT id FROM users WHERE (username=:username OR email=:email) AND id<>:id LIMIT 1', ['username' => $data['username'], 'email' => $data['email'], 'id' => $id ?? 0])->fetch()) {
        throw new InvalidArgumentException('Username atau email sudah digunakan akun lain.');
    }
    return $data;
}

function protect_last_admin(array $old, string $role, int $active): void
{
    if ($old['role'] === 'admin' && (int) $old['aktif'] === 1 && ($role !== 'admin' || $active !== 1)
        && (int) panel_query("SELECT COUNT(*) FROM users WHERE role='admin' AND aktif=1")->fetchColumn() <= 1) {
        throw new InvalidArgumentException('Admin aktif terakhir tidak boleh dinonaktifkan atau diturunkan rolenya.');
    }
}

/** Tidak menyimpan password atau seluruh POST ke session saat validasi gagal. */
function account_failure(Throwable $exception, string $path): never
{
    if ($exception instanceof InvalidArgumentException) { $message = $exception->getMessage(); }
    elseif ($exception instanceof PDOException && $exception->getCode() === '23000') { $message = 'Username atau email sudah digunakan. Silakan periksa kembali.'; }
    else { error_log('Perubahan akun gagal: ' . get_class($exception) . ' ' . $exception->getCode()); $message = 'Perubahan belum tersimpan. Periksa database lalu coba kembali.'; }
    panel_message($message, $path);
}
