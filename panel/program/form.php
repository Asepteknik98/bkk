<?php
if(!isset($user,$data)){http_response_code(403);exit('Akses ditolak.');}
$errors=[];if(isset($_SESSION['program_form'])&&($_SESSION['program_form']['id']??null)===$id){$data=array_replace($data,$_SESSION['program_form']['data']);$errors=$_SESSION['program_form']['errors'];unset($_SESSION['program_form']);}
$images=panel_query("SELECT * FROM media WHERE mime_type IN ('image/jpeg','image/png','image/webp') ORDER BY id DESC LIMIT 200")->fetchAll();
if($data['media_id']!==''&&!in_array((int)$data['media_id'],array_map('intval',array_column($images,'id')),true)){$selected=panel_query('SELECT * FROM media WHERE id=:id',['id'=>(int)$data['media_id']])->fetch();if($selected){$images[]=$selected;}}
panel_start($user,$id?'Edit Program':'Tambah Program','program');
?>
<div class="page-actions"><h1><?= $id?'Edit Program':'Tambah Program' ?></h1><a href="<?= e(base_url('panel/program/')) ?>">← Daftar program</a></div>
<?php if($errors):?><div class="alert" role="alert"><strong>Program belum disimpan.</strong><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><p>Pilih kembali berkas unggahan jika diperlukan.</p></div><?php endif;?>
<form class="editor-form card news-editor program-editor" method="post" action="<?= e(base_url('panel/program/simpan.php')) ?>" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id??'' ?>">
<div class="news-editor-main">
<label for="judul">Judul</label><input id="judul" name="judul" maxlength="255" value="<?= e($data['judul']) ?>" required>
<label for="slug">Slug (opsional)</label><input id="slug" name="slug" maxlength="191" value="<?= e($data['slug']) ?>"><small>Kosongkan untuk membuat dari judul. Slug dibuat unik. Mengubah slug atau kategori mengubah tautan publik.</small>
<label for="ringkasan">Ringkasan</label><textarea id="ringkasan" name="ringkasan" rows="2" maxlength="1000" required><?= e($data['ringkasan']) ?></textarea>
<label for="isi">Isi program</label><textarea id="isi" name="isi" rows="7" maxlength="100000" required><?= e($data['isi']) ?></textarea><small>Gunakan teks biasa dan baris kosong untuk paragraf. Untuk Kelas Melanjutkan, jelaskan tujuan dalam negeri/luar negeri, kampus, dan informasi beasiswa yang telah diverifikasi. HTML ditampilkan sebagai teks.</small>
</div><div class="news-editor-settings"><h2>Pengaturan program</h2>
<label for="kategori">Kategori</label><select id="kategori" name="kategori" required><option value="">Pilih kategori</option><?php foreach(program_categories() as $value=>$info):?><option value="<?= e($value) ?>" <?= $data['kategori']===$value?'selected':'' ?>><?= e($info['title']) ?></option><?php endforeach;?></select>
<label for="tautan_pendaftaran">Tautan pendaftaran (opsional)</label><input type="url" id="tautan_pendaftaran" name="tautan_pendaftaran" maxlength="2048" value="<?= e($data['tautan_pendaftaran']) ?>" placeholder="https://..."><small>Hanya http/https; kosongkan jika belum tersedia.</small>
<label for="status">Status</label><select id="status" name="status"><?php foreach(['draft'=>'Draft — belum tampil di publik','published'=>'Published — tampil di publik'] as $value=>$label):?><option value="<?= $value ?>" <?= $data['status']===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select>
<label for="gambar">Unggah gambar baru</label><input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small>JPG/PNG/WebP maksimal 5 MB. Unggahan baru mengalahkan pilihan katalog.</small>
<?php if($data['gambar']):?><div class="current-image"><img src="<?= e(content_image($data['gambar'])) ?>" alt="Gambar program saat ini" width="240"><label class="check-label"><input type="checkbox" name="hapus_gambar" value="1" <?= $data['hapus_gambar']?'checked':'' ?>> Lepaskan gambar saat ini</label></div><?php endif;?>
<label for="media_id">Atau pilih gambar katalog (200 terbaru)</label><select id="media_id" name="media_id"><option value="">Pertahankan gambar / tanpa gambar</option><?php foreach($images as $image):if(!media_is_image($image)){continue;}?><option value="<?= $image['id'] ?>" <?= (string)$image['id']===$data['media_id']?'selected':'' ?>>#<?= $image['id'] ?> — <?= e($image['nama_asli']) ?></option><?php endforeach;?></select><small>Gambar yang dilepas/diganti tetap tersedia di <a href="<?= e(base_url('panel/media/')) ?>">katalog media</a>.</small>
</div><div class="form-actions"><button type="submit">Simpan program</button><a href="<?= e(base_url('panel/program/')) ?>">Batal</a></div></form>
<?php panel_end($user);?>
