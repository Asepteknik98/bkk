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
<div class="personal-account-sheet"><header class="personal-account-heading"><h1><?= panel_icon('akun') ?> Akun Pribadi</h1><span><?= panel_icon('lock') ?> <?= e($user['role']==='admin'?'Admin':'Staf BKK') ?></span></header>
<form class="editor-form card personal-profile-form" method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<section class="personal-photo-column" aria-labelledby="personal-photo-title"><h2 id="personal-photo-title"><?= panel_icon('media') ?> Foto profil <small>Opsional</small></h2><?php account_photo_field($user); ?></section><section class="personal-identity-section" aria-labelledby="personal-identity-title"><h2 id="personal-identity-title"><?= panel_icon('identity') ?> Identitas akun</h2><div class="personal-fields">
<?php foreach(['nama'=>'Nama lengkap','username'=>'Username','email'=>'Email'] as $key=>$label): ?><div class="personal-field"><label for="<?= $key ?>"><?= panel_icon(['nama'=>'akun','username'=>'identity','email'=>'mail'][$key]) ?> <?= e($label) ?></label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $key==='email'?'email':'text' ?>" maxlength="<?= $key==='username'?50:($key==='nama'?150:191) ?>" value="<?= e($user[$key]) ?>" required></div><?php endforeach; ?>
<small class="personal-hint">Username 3–50 huruf kecil/angka/titik/garis, diawali huruf atau angka.</small></div></section><div class="form-actions"><button type="submit"><?= panel_icon('save') ?> Simpan profil</button><a href="<?= e(base_url('panel/akun/ubah-password.php')) ?>"><?= panel_icon('lock') ?> Ubah password</a></div></form></div>

<?php panel_end($user); ?>
