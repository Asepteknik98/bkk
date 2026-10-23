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
    ['Pengumuman publik','news',['public_announcement','public_announcement_link_text','public_announcement_link_url']],
    ['Media sosial & profil','home',['social_instagram','social_facebook','social_youtube','profile_description']],
];
$fields=settings_fields();
foreach($groups as [$heading,$icon,$keys]): ?>
<section class="card settings-section"><h2><?= panel_icon($icon) ?> <?= e($heading) ?></h2><div class="settings-fields">
<?php foreach($keys as $key): [$label,$max,$required]=$fields[$key]; ?>
<div class="settings-field <?= in_array($key,['contact_address','profile_description','public_announcement'],true)?'settings-wide':'' ?>"><label for="<?= $key ?>"><?= e($label) ?></label>
<?php if(in_array($key,['contact_address','profile_description','public_announcement'],true)): ?><textarea id="<?= $key ?>" name="<?= $key ?>" rows="<?= $key==='profile_description'?4:($key==='public_announcement'?3:2) ?>" maxlength="<?= $max ?>"><?= e($data[$key]) ?></textarea>
<?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= str_starts_with($key,'social_')||$key==='public_announcement_link_url'?'url':($key==='contact_email'?'email':'text') ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= $required?'required':'' ?>><?php endif; ?>
<?php if($key==='public_announcement'): ?><small>Masukkan URL langsung untuk mengubahnya menjadi link, atau isi teks tombol dan URL pada kolom di bawah.</small><?php endif; ?>
<?php if($key==='public_announcement_link_text'): ?><small>Contoh: Daftar sekarang. Isi bersama URL tujuan.</small><?php endif; ?>
<?php if($key==='public_announcement_link_url'): ?><small>Gunakan URL lengkap yang diawali https:// atau http://.</small><?php endif; ?>
</div><?php endforeach; ?></div></section>
<?php endforeach; ?>
<section class="card settings-logos"><h2><?= panel_icon('media') ?> Logo website</h2><div class="settings-logo-grid">
<div class="settings-logo-field"><?php management_image_field('bkk_logo','Logo BKK',setting('bkk_logo')); ?></div>
<div class="settings-logo-field"><?php management_image_field('school_logo','Logo sekolah',setting('school_logo')); ?></div>
</div></section>
<div class="settings-save"><button type="submit">Simpan pengaturan</button></div>
</form></div>
<?php panel_end($user); ?>
