<?php
if(!isset($user,$data)){http_response_code(403);exit;}
if(isset($_SESSION['mitra_form'])&&$_SESSION['mitra_form']['id']===$id){$data=array_replace($data,$_SESSION['mitra_form']['data']);unset($_SESSION['mitra_form']);}
panel_start($user,$id?'Edit Mitra':'Tambah Mitra','mitra');
?>
<div class="partner-form-workspace">
<h1><?= panel_icon('mitra') ?> <?= $id?'Edit Mitra':'Tambah Mitra' ?></h1><form class="editor-form card news-editor partner-editor" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/mitra/simpan.php')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id??'' ?>">
<div class="news-editor-main"><label for="nama"><?= panel_icon('mitra') ?> Nama mitra</label><input id="nama" name="nama" maxlength="200" value="<?= e($data['nama']) ?>" required>
<label for="jenis"><?= panel_icon('master') ?> Jenis</label><select id="jenis" name="jenis"><?php foreach(['perusahaan'=>'Perusahaan','kampus'=>'Perguruan tinggi'] as $key=>$label): ?><option value="<?= $key ?>" <?= $data['jenis']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select>
<label for="website"><?= panel_icon('link') ?> Website (opsional)</label><input id="website" name="website" type="url" maxlength="2048" value="<?= e($data['website']) ?>">
<label for="deskripsi"><?= panel_icon('message') ?> Deskripsi (teks biasa)</label><textarea id="deskripsi" name="deskripsi" rows="3" maxlength="10000"><?= e($data['deskripsi']) ?></textarea>
</div><div class="news-editor-settings"><h2><?= panel_icon('media') ?> Status &amp; logo</h2><label for="aktif"><?= panel_icon('check-circle') ?> Status</label><select id="aktif" name="aktif"><option value="1" <?= $data['aktif']?'selected':'' ?>>Aktif</option><option value="0" <?= !$data['aktif']?'selected':'' ?>>Nonaktif</option></select>
<?php management_image_field('logo','Logo mitra',$data['logo']); ?>
</div><div class="form-actions"><button type="submit"><?= panel_icon('save') ?> Simpan mitra</button><a href="<?= e(base_url('panel/mitra/')) ?>"><?= panel_icon('back') ?> Kembali</a></div></form>
</div>
<?php panel_end($user); ?>
