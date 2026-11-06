<?php
if(!isset($user,$data)){http_response_code(403);exit;}
if(isset($_SESSION['mitra_form'])&&$_SESSION['mitra_form']['id']===$id){$data=array_replace($data,$_SESSION['mitra_form']['data']);unset($_SESSION['mitra_form']);}
$profileReady=partner_profiles_ready();
panel_start($user,$id?'Edit Mitra':'Tambah Mitra','mitra');
?>
<div class="partner-form-workspace">
<p><a href="<?= e(base_url('panel/mitra/')) ?>">Semua mitra</a> · <a href="<?= e(base_url('panel/mitra/kampus.php')) ?>">Direktori Kampus Dalam Negeri</a></p>
<h1><?= panel_icon('mitra') ?> <?= $id?'Edit Mitra':'Tambah Mitra' ?></h1><form class="editor-form card news-editor partner-editor" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/mitra/simpan.php')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id??'' ?>">
<div class="news-editor-main"><label for="nama"><?= panel_icon('mitra') ?> Nama mitra</label><input id="nama" name="nama" maxlength="200" value="<?= e($data['nama']) ?>" required>
<label for="jenis"><?= panel_icon('master') ?> Jenis</label><select id="jenis" name="jenis"><?php foreach(['perusahaan'=>'Perusahaan','kampus'=>'Perguruan tinggi'] as $key=>$label): ?><option value="<?= $key ?>" <?= $data['jenis']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select>
<label for="website"><?= panel_icon('link') ?> Website (opsional)</label><input id="website" name="website" type="url" maxlength="2048" value="<?= e($data['website']) ?>">
<label for="deskripsi"><?= panel_icon('message') ?> Deskripsi (teks biasa)</label><textarea id="deskripsi" name="deskripsi" rows="3" maxlength="10000"><?= e($data['deskripsi']) ?></textarea>
<?php if($profileReady): ?><input type="hidden" name="profile_form" value="1"><input type="hidden" name="version" value="<?= (int)($data['version']??0) ?>">
<h2>Profil industri / kampus</h2><p>Perusahaan wajib memilih klaster. Kampus pada direktori ini menggunakan negara Indonesia. Kontak PIC hanya tersedia di panel pengelola.</p>
<label for="klaster">Klaster industri (untuk perusahaan)</label><select id="klaster" name="klaster"><option value="">Pilih / tidak berlaku untuk kampus</option><?php foreach(partner_clusters() as $key=>$label): ?><option value="<?= $key ?>" <?= ($data['klaster']??'')===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select>
<?php foreach(partner_profile_fields() as $key=>[$label,$max]): ?><label for="<?= $key ?>"><?= e($label) ?></label><?php if($key==='alamat_lengkap'): ?><textarea id="<?= $key ?>" name="<?= $key ?>" rows="3" maxlength="<?= $max ?>" required placeholder="Jalan/nomor, desa/kelurahan, kecamatan, kota/kabupaten, provinsi, kode pos"><?= e($data[$key]??'') ?></textarea><?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]??'') ?>" <?= $key==='negara'?'required':'' ?>><?php endif; ?><?php endforeach; ?>
<p>Bidang usaha wajib untuk perusahaan. Nama, jabatan, dan kontak PIC boleh dilengkapi saat informasinya tersedia.</p>
<?php else: ?><p class="notice">Impor database/updates/022_partner_profiles_mou.sql untuk mengaktifkan profil lengkap dan direktori kampus.</p><?php endif; ?>
</div><div class="news-editor-settings"><h2><?= panel_icon('media') ?> Status &amp; logo</h2><label for="aktif"><?= panel_icon('check-circle') ?> Status</label><select id="aktif" name="aktif"><option value="1" <?= $data['aktif']?'selected':'' ?>>Aktif</option><option value="0" <?= !$data['aktif']?'selected':'' ?>>Nonaktif</option></select>
<?php management_image_field('logo','Logo mitra',$data['logo']); ?>
</div><div class="form-actions"><?php if($profileReady): ?><input type="hidden" name="profile_complete" value="1"><?php endif; ?><button type="submit"><?= panel_icon('save') ?> Simpan mitra</button><a href="<?= e(base_url('panel/mitra/')) ?>"><?= panel_icon('back') ?> Kembali</a></div></form>
</div>
<?php panel_end($user); ?>
