<?php
declare(strict_types=1);
require_once __DIR__ . '/panel.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/upload.php';

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

/** Store a validated photo and retain its media reference after replacement. */
function account_photo(array $old, int $actorId, ?array &$upload): ?string
{
    if (isset($_FILES['profile_photo']) && !is_array($_FILES['profile_photo'])) {
        throw new InvalidArgumentException('Data foto tidak valid.');
    }
    $upload=store_upload($_FILES['profile_photo']??null,'profil');
    if ($upload) {register_media($upload,$actorId); return $upload['path'];}
    return ($_POST['remove_photo']??'')==='1'?null:($old['foto_profil']??null);
}

function account_photo_url(array $account): ?string
{
    $path=$account['foto_profil']??null;
    return is_string($path) && preg_match('/\.(jpg|png|webp)$/D',$path) && managed_upload_path($path) ? base_url($path) : null;
}

function account_photo_field(array $account): void
{
    $photo=account_photo_url($account);
    ?>
    <div class="profile-photo-field">
        <?php if($photo): ?><img class="profile-photo-preview" src="<?= e($photo) ?>" alt="Foto profil saat ini" width="64" height="64"><?php endif; ?>
        <div><label for="profile_photo">Foto profil</label><input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"><small>JPG, PNG, atau WebP, maksimal 5 MB.</small>
        <?php if($photo): ?><label class="check-label"><input type="checkbox" name="remove_photo" value="1"> Hapus foto saat ini</label><?php endif; ?></div>
    </div>
    <?php
}
