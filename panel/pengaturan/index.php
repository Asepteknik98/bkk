<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin');$data=[];
foreach(settings_fields() as $key=>[$label,$max,$required]){$data[$key]=setting($key,$key==='school_name'?'SMK JAYA BUANA':'');}
if(isset($_SESSION['settings_form'])){$data=array_replace($data,$_SESSION['settings_form']);unset($_SESSION['settings_form']);}
panel_start($user,'Pengaturan','pengaturan');
?>
<div class="settings-page">
<div class="page-actions"><div><p class="eyebrow">Konfigurasi portal</p><h1><?= panel_icon('pengaturan') ?> Pengaturan Website</h1></div></div>
<p class="settings-note">Gunakan informasi terverifikasi. Kontak yang belum tersedia boleh dikosongkan.</p>
<form class="settings-form" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/pengaturan/simpan.php')) ?>"><?= csrf_field() ?>
<?php
$groups=[
    ['Identitas & kontak','akun',['site_name','school_name','contact_phone','contact_email','contact_address']],
    ['Media sosial','home',array_keys(social_networks())],
];
$fields=settings_fields();
foreach($groups as [$heading,$icon,$keys]): ?>
<section class="card settings-section <?= str_starts_with($keys[0],'social_')?'settings-social':'' ?>"><h2><?= panel_icon($icon) ?> <?= e($heading) ?></h2><div class="settings-fields">
<?php foreach($keys as $key): [$label,$max,$required]=$fields[$key]; ?>
<div class="settings-field <?= in_array($key,['contact_address','profile_description'],true)?'settings-wide':'' ?>"><label for="<?= $key ?>"><?= str_starts_with($key,'social_') ? social_icon($key) : panel_icon(['site_name'=>'identity','school_name'=>'program','contact_phone'=>'phone','contact_email'=>'mail','contact_address'=>'location','social_instagram'=>'instagram','social_facebook'=>'facebook','social_youtube'=>'youtube','profile_description'=>'message'][$key] ?? 'pengaturan') ?> <?= e(str_starts_with($key,'social_') ? social_networks()[$key] : $label) ?></label>
<?php if(in_array($key,['contact_address','profile_description'],true)): ?><textarea id="<?= $key ?>" name="<?= $key ?>" rows="<?= $key==='profile_description'?4:2 ?>" maxlength="<?= $max ?>"><?= e($data[$key]) ?></textarea>
<?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= str_starts_with($key,'social_')?'url':($key==='contact_email'?'email':'text') ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= str_starts_with($key,'social_')?'placeholder="https://?"':'' ?> <?= $required?'required':'' ?>><?php endif; ?>
</div><?php endforeach; ?></div></section>
<?php endforeach; ?>
<details class="card settings-profile"><summary><?= panel_icon('message') ?> Profil BKK</summary><label for="profile_description" class="sr-only">Isi profil BKK</label><textarea id="profile_description" name="profile_description" rows="3" maxlength="50000"><?= e($data['profile_description']) ?></textarea></details>
<section class="card settings-logos"><h2><?= panel_icon('media') ?> Logo website</h2><div class="settings-logo-grid">
<div class="settings-logo-field"><?php management_image_field('bkk_logo','Logo BKK',setting('bkk_logo')); ?></div>
<div class="settings-logo-field"><?php management_image_field('school_logo','Logo sekolah',setting('school_logo')); ?></div>
</div></section>
<div class="settings-save"><button type="submit"><?= panel_icon('save') ?> Simpan pengaturan</button></div>
</form></div>
<?php panel_end($user); ?>
