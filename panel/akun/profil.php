<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/accounts.php';
$user=require_role('admin','staf_bkk');
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_post_csrf();
    try {
        account_change((int)$user['id'],false,static function(array $actor): void {
            $data=account_identity($_POST,(int)$actor['id']);
            panel_query('UPDATE users SET nama=:nama,username=:username,email=:email WHERE id=:id',$data+['id'=>(int)$actor['id']]);
        });
        panel_message('Profil pribadi berhasil diperbarui.','panel/akun/profil.php');
    } catch (Throwable $exception) { account_failure($exception,'panel/akun/profil.php'); }
}
panel_start($user,'Akun Pribadi','akun');
?>
<h1>Akun Pribadi</h1><p>Role: <?= e($user['role']) ?></p>
<form class="editor-form card" method="post"><?= csrf_field() ?>
<?php foreach(['nama'=>'Nama lengkap','username'=>'Username','email'=>'Email'] as $key=>$label): ?><label for="<?= $key ?>"><?= $label ?></label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $key==='email'?'email':'text' ?>" maxlength="<?= $key==='username'?50:($key==='nama'?150:191) ?>" value="<?= e($user[$key]) ?>" required><?php endforeach; ?>
<small>Username 3–50 huruf kecil/angka/titik/garis, diawali huruf atau angka.</small><button type="submit">Simpan profil</button></form>
<p><a href="<?= e(base_url('panel/akun/ubah-password.php')) ?>">Ubah password</a></p>
<?php panel_end($user); ?>
