<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/accounts.php';
$user=require_role('admin','staf_bkk');
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_post_csrf();
    $upload=null;
    try {
        account_change((int)$user['id'],false,static function(array $actor) use (&$upload): void {
            $data=account_identity($_POST,(int)$actor['id']);
            $photo=account_photo($actor,(int)$actor['id'],$upload);
            panel_query('UPDATE users SET nama=:nama,username=:username,email=:email,foto_profil=:foto WHERE id=:id',$data+['foto'=>$photo,'id'=>(int)$actor['id']]);
        });
        panel_message('Profil pribadi berhasil diperbarui.','panel/akun/profil.php');
    } catch (Throwable $exception) { discard_upload($upload); account_failure($exception,'panel/akun/profil.php'); }
}
panel_start($user,'Akun Pribadi','akun');
?>
<h1>Akun Pribadi</h1><p>Role: <?= e($user['role']) ?></p>
<form class="editor-form card personal-profile-form" method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="personal-photo-column"><?php account_photo_field($user); ?></div><div class="personal-fields">
<?php foreach(['nama'=>'Nama lengkap','username'=>'Username','email'=>'Email'] as $key=>$label): ?><div class="personal-field"><label for="<?= $key ?>"><?= $label ?></label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $key==='email'?'email':'text' ?>" maxlength="<?= $key==='username'?50:($key==='nama'?150:191) ?>" value="<?= e($user[$key]) ?>" required></div><?php endforeach; ?>
<small class="personal-hint">Username 3–50 huruf kecil/angka/titik/garis, diawali huruf atau angka.</small></div><div class="form-actions"><button type="submit">Simpan profil</button><a href="<?= e(base_url('panel/akun/ubah-password.php')) ?>">Ubah password</a></div></form>

<?php panel_end($user); ?>
