<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/accounts.php';
require_once APP_ROOT.'/app/helpers/login-throttle.php';
$user=require_role('admin','staf_bkk');
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_post_csrf();
    $retry=login_attempt('password-change:'.$user['id'],10);
    if ($retry>0) { http_response_code(429); header('Retry-After: '.$retry); exit('Terlalu banyak percobaan. Coba kembali setelah 15 menit.'); }
    try {
        $version=account_change((int)$user['id'],false,static function(array $actor): int {
            $old=is_string($_POST['old_password']??null)?$_POST['old_password']:'';
            if (strlen($old)>72 || !password_verify($old,$actor['password_hash'])) { throw new InvalidArgumentException('Password lama tidak sesuai.'); }
            $hash=account_password(is_string($_POST['password']??null)?$_POST['password']:'',is_string($_POST['confirmation']??null)?$_POST['confirmation']:'');
            panel_query('UPDATE users SET password_hash=:hash,session_version=session_version+1 WHERE id=:id',['hash'=>$hash,'id'=>(int)$actor['id']]);
            return (int)$actor['session_version']+1;
        });
        session_regenerate_id(true); $_SESSION['auth_version']=$version;
        panel_message('Password diperbarui. Sesi perangkat lain dibatalkan.','panel/akun/profil.php');
    } catch (Throwable $exception) { account_failure($exception,'panel/akun/ubah-password.php'); }
}
panel_start($user,'Ubah Password','akun');
?>
<h1>Ubah Password</h1><form class="editor-form card" method="post"><?= csrf_field() ?>
<label for="old_password">Password lama</label><input id="old_password" name="old_password" type="password" autocomplete="current-password" maxlength="72" required>
<label for="password">Password baru (12–72 byte)</label><input id="password" name="password" type="password" autocomplete="new-password" maxlength="72" required>
<label for="confirmation">Ulangi password baru</label><input id="confirmation" name="confirmation" type="password" autocomplete="new-password" maxlength="72" required><button type="submit">Ubah password</button></form>
<?php panel_end($user); ?>
