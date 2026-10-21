<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin');$data=[];
foreach(settings_fields() as $key=>[$label,$max,$required]){$data[$key]=setting($key,$key==='school_name'?'SMK JAYA BUANA':'');}
if(isset($_SESSION['settings_form'])){$data=array_replace($data,$_SESSION['settings_form']);unset($_SESSION['settings_form']);}
panel_start($user,'Pengaturan','pengaturan');
?>
<h1>Pengaturan Website</h1><p>Isi hanya informasi yang telah diverifikasi. Kontak yang belum tersedia boleh dikosongkan. Profil menggunakan teks biasa.</p>
<form class="editor-form card" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/pengaturan/simpan.php')) ?>"><?= csrf_field() ?>
<?php foreach(settings_fields() as $key=>[$label,$max,$required]): ?><label for="<?= $key ?>"><?= e($label) ?></label>
<?php if(in_array($key,['contact_address','profile_description'],true)): ?><textarea id="<?= $key ?>" name="<?= $key ?>" rows="<?= $key==='profile_description'?10:3 ?>" maxlength="<?= $max ?>"><?= e($data[$key]) ?></textarea>
<?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= str_starts_with($key,'social_')?'url':($key==='contact_email'?'email':'text') ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= $required?'required':'' ?>><?php endif; ?>
<?php endforeach; ?>
<?php management_image_field('bkk_logo','Logo BKK',setting('bkk_logo'));management_image_field('school_logo','Logo sekolah',setting('school_logo')); ?>
<button type="submit">Simpan pengaturan</button></form>
<?php panel_end($user); ?>
