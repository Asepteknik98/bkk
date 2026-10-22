<?php
if(!isset($user,$data)){http_response_code(403);exit;}
if(isset($_SESSION['mitra_form'])&&$_SESSION['mitra_form']['id']===$id){$data=array_replace($data,$_SESSION['mitra_form']['data']);unset($_SESSION['mitra_form']);}
panel_start($user,$id?'Edit Mitra':'Tambah Mitra','mitra');
?>
<h1><?= $id?'Edit Mitra':'Tambah Mitra' ?></h1><form class="editor-form card news-editor partner-editor" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/mitra/simpan.php')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id??'' ?>">
<div class="news-editor-main"><label for="nama">Nama mitra</label><input id="nama" name="nama" maxlength="200" value="<?= e($data['nama']) ?>" required>
<label for="jenis">Jenis</label><select id="jenis" name="jenis"><?php foreach(['perusahaan'=>'Perusahaan','kampus'=>'Perguruan tinggi'] as $key=>$label): ?><option value="<?= $key ?>" <?= $data['jenis']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select>
<label for="website">Website (opsional)</label><input id="website" name="website" type="url" maxlength="2048" value="<?= e($data['website']) ?>">
<label for="deskripsi">Deskripsi (teks biasa)</label><textarea id="deskripsi" name="deskripsi" rows="3" maxlength="10000"><?= e($data['deskripsi']) ?></textarea>
</div><div class="news-editor-settings"><h2>Status &amp; logo</h2><label for="aktif">Status</label><select id="aktif" name="aktif"><option value="1" <?= $data['aktif']?'selected':'' ?>>Aktif</option><option value="0" <?= !$data['aktif']?'selected':'' ?>>Nonaktif</option></select>
<?php management_image_field('logo','Logo mitra',$data['logo']); ?>
</div><div class="form-actions"><button type="submit">Simpan mitra</button><a href="<?= e(base_url('panel/mitra/')) ?>">Kembali</a></div></form>
<?php panel_end($user); ?>
