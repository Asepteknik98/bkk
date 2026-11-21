<?php
if(!isset($user,$data)){http_response_code(403);exit;}
if(isset($_SESSION['mitra_form'])&&$_SESSION['mitra_form']['id']===$id){$data=array_replace($data,$_SESSION['mitra_form']['data']);unset($_SESSION['mitra_form']);}
$profileReady=partner_profiles_ready();
panel_start($user,$id?'Edit Mitra':'Tambah Mitra','mitra');
?>
<div class="partner-form-workspace partner-form-sheet">
<header class="partner-detail-heading"><div><h1><?= panel_icon('mitra') ?> <?= $id?'Edit Mitra':'Tambah Mitra' ?></h1><p>Lengkapi identitas, profil, dan kontak internal mitra.</p></div><nav aria-label="Navigasi mitra"><a href="<?= e(base_url('panel/mitra/')) ?>"><?= panel_icon('back') ?> Daftar mitra</a><a href="<?= e(base_url('panel/mitra/kampus.php')) ?>"><?= panel_icon('program') ?> Direktori kampus</a></nav></header>
<form class="partner-editor partner-sheet-form" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/mitra/simpan.php')) ?>">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= $id??'' ?>">
<div class="partner-form-columns"><div>
<section class="partner-form-section"><h2><?= panel_icon('identity') ?> 1. Identitas mitra</h2><div class="partner-field-grid">
<div class="partner-field"><label for="nama"><?= panel_icon('mitra') ?> Nama mitra *</label><input id="nama" name="nama" maxlength="200" value="<?= e($data['nama']) ?>" required></div>
<div class="partner-field"><label for="jenis"><?= panel_icon('master') ?> Jenis</label><select id="jenis" name="jenis"><?php foreach(['perusahaan'=>'Perusahaan','kampus'=>'Perguruan tinggi'] as $key=>$label): ?><option value="<?= $key ?>" <?= $data['jenis']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></div>
<div class="partner-field partner-field-wide"><label for="website"><?= panel_icon('link') ?> Website (opsional)</label><input id="website" name="website" type="url" maxlength="2048" value="<?= e($data['website']) ?>" placeholder="https://"></div>
<div class="partner-field partner-field-wide"><label for="deskripsi"><?= panel_icon('message') ?> Deskripsi publik</label><textarea id="deskripsi" name="deskripsi" rows="3" maxlength="10000"><?= e($data['deskripsi']) ?></textarea><small>Teks biasa; tampil untuk mitra aktif.</small></div>
</div></section>
<?php if($profileReady): ?><input type="hidden" name="profile_form" value="1"><input type="hidden" name="version" value="<?= (int)($data['version']??0) ?>">
<section class="partner-form-section"><h2><?= panel_icon('location') ?> 2. Profil industri / kampus</h2><div class="partner-field-grid">
<div class="partner-field partner-field-wide"><label for="klaster"><?= panel_icon('lowongan') ?> Klaster perusahaan *</label><select id="klaster" name="klaster"><option value="">Pilih klaster / tidak berlaku untuk kampus</option><?php foreach(partner_clusters() as $key=>$label): ?><option value="<?= $key ?>" <?= ($data['klaster']??'')===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select><small>Wajib untuk perusahaan. Kampus tidak menggunakan klaster.</small></div>
<?php foreach(['bidang_usaha','negara','alamat_lengkap'] as $key): [$label,$max]=partner_profile_fields()[$key]; ?>
<div class="partner-field <?= $key==='alamat_lengkap'?'partner-field-wide':'' ?>"><label for="<?= $key ?>"><?= panel_icon($key==='alamat_lengkap'?'location':($key==='negara'?'home':'lowongan')) ?> <?= e($label) ?><?= in_array($key,['negara','alamat_lengkap'],true)?' *':'' ?></label><?php if($key==='alamat_lengkap'): ?><textarea id="<?= $key ?>" name="<?= $key ?>" rows="3" maxlength="<?= $max ?>" required placeholder="Jalan, desa/kelurahan, kecamatan, kota/kabupaten, provinsi, kode pos"><?= e($data[$key]??'') ?></textarea><?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]??'') ?>" <?= $key==='negara'?'required':'' ?>><?php endif; ?></div>
<?php endforeach; ?>
</div><p class="partner-form-help">Bidang usaha wajib untuk perusahaan. Direktori kampus dalam negeri menggunakan negara Indonesia.</p></section>
<?php else: ?><p class="notice">Impor database/updates/022_partner_profiles_mou.sql untuk mengaktifkan profil lengkap dan direktori kampus.</p><?php endif; ?>
</div><div>
<?php if($profileReady): ?><section class="partner-form-section"><h2><?= panel_icon('akun') ?> 3. Penanggung jawab (PIC)</h2><p class="partner-form-help"><?= panel_icon('lock') ?> Opsional, hanya tersedia di panel pengelola.</p><div class="partner-field-grid">
<?php foreach(['pic_nama','pic_jabatan','pic_kontak'] as $key): [$label,$max]=partner_profile_fields()[$key]; ?><div class="partner-field partner-field-wide"><label for="<?= $key ?>"><?= panel_icon($key==='pic_kontak'?'phone':'akun') ?> <?= e($label) ?></label><input id="<?= $key ?>" name="<?= $key ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]??'') ?>"></div><?php endforeach; ?>
</div></section><?php endif; ?>
<section class="partner-form-section"><h2><?= panel_icon('media') ?> <?= $profileReady?'4':'2' ?>. Status &amp; logo</h2><div class="partner-field"><label for="aktif"><?= panel_icon('check-circle') ?> Status mitra</label><select id="aktif" name="aktif"><option value="1" <?= $data['aktif']?'selected':'' ?>>Aktif</option><option value="0" <?= !$data['aktif']?'selected':'' ?>>Nonaktif</option></select></div><div class="partner-logo-fields"><?php management_image_field('logo','Logo mitra',$data['logo']); ?></div></section>
</div></div>
<div class="form-actions"><?php if($profileReady): ?><input type="hidden" name="profile_complete" value="1"><?php endif; ?><span>* Wajib diisi</span><a href="<?= e(base_url('panel/mitra/')) ?>"><?= panel_icon('back') ?> Batal</a><button type="submit"><?= panel_icon('save') ?> Simpan mitra</button></div>
</form></div>
<?php panel_end($user); ?>
