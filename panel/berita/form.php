<?php
if (!isset($user, $data)) { http_response_code(403); exit('Akses ditolak.'); }
require_once APP_ROOT . '/app/helpers/public.php';
$errors = [];
if (isset($_SESSION['news_form']) && ($_SESSION['news_form']['id'] ?? null) === $id) {
    $data = array_replace($data, $_SESSION['news_form']['data']);
    $errors = $_SESSION['news_form']['errors'];
    unset($_SESSION['news_form']);
}
$mediaTotal = (int) panel_query("SELECT COUNT(*) FROM media WHERE mime_type IN ('image/jpeg','image/png','image/webp')")->fetchColumn();
$images = panel_query("SELECT * FROM media WHERE mime_type IN ('image/jpeg','image/png','image/webp') ORDER BY id DESC LIMIT 200")->fetchAll();
// Sertakan pilihan lama jika berada di luar 200 gambar terbaru.
if ($data['media_id'] !== '' && !in_array((int) $data['media_id'], array_map('intval', array_column($images, 'id')), true)) {
    $selected = panel_query('SELECT * FROM media WHERE id=:id', ['id' => (int) $data['media_id']])->fetch();
    if ($selected) { $images[] = $selected; }
}
panel_start($user, $id ? 'Edit Berita' : 'Tambah Berita', 'berita');
?>
<div class="page-actions"><h1><?= $id ? 'Edit Berita' : 'Tambah Berita' ?></h1><a href="<?= e(base_url('panel/berita/')) ?>">← Daftar berita</a></div>
<?php if ($errors): ?><div class="alert" role="alert"><strong>Berita belum disimpan.</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="editor-form card news-editor" method="post" action="<?= e(base_url('panel/berita/simpan.php')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?? '' ?>">
    <div class="news-editor-main">
    <label for="judul">Judul <span aria-hidden="true">*</span></label><input id="judul" name="judul" maxlength="255" value="<?= e($data['judul']) ?>" required>
    <label for="slug">Slug (opsional)</label><input id="slug" name="slug" maxlength="191" value="<?= e($data['slug']) ?>" aria-describedby="slug-hint"><small id="slug-hint">Kosongkan untuk membuat dari judul. Slug dinormalisasi; akhiran angka ditambahkan jika sudah dipakai. Mengubah slug akan mengubah tautan publik.</small>
    <label for="ringkasan">Ringkasan <span aria-hidden="true">*</span></label><textarea id="ringkasan" name="ringkasan" rows="2" maxlength="1000" required><?= e($data['ringkasan']) ?></textarea>
    <label for="isi">Isi berita <span aria-hidden="true">*</span></label><textarea id="isi" name="isi" rows="7" maxlength="100000" aria-describedby="isi-hint" required><?= e($data['isi']) ?></textarea><small id="isi-hint">Gunakan teks biasa. Pisahkan paragraf dengan baris kosong. Tag HTML akan tampil sebagai teks, bukan dijalankan.</small>
    </div><div class="news-editor-settings">
    <h2>Publikasi &amp; gambar</h2>
    <label class="check-label"><input type="checkbox" name="popup" value="1" <?= !empty($data['popup']) ? 'checked' : '' ?>> Tampilkan sebagai popup</label>
    <small>Popup memakai judul, isi, dan gambar berita ini, dan muncul sekali per sesi tab browser. Pindah menu dan refresh tidak menampilkan popup kembali. Cantumkan URL website lengkap (https://...) di isi berita agar tombol Kunjungi website muncul; tautan pertama digunakan. Untuk lowongan kerja, tuliskan posisi, persyaratan, cara melamar, dan batas pendaftaran. Hanya tampil setelah Published. Memilih berita ini menggantikan pilihan popup sebelumnya. Hilangkan centang untuk menonaktifkannya.</small>
    <div class="form-columns"><div><label for="status">Status</label><select id="status" name="status"><option value="draft" <?= $data['status'] === 'draft' ? 'selected' : '' ?>>Draft — belum tampil di publik</option><option value="published" <?= $data['status'] === 'published' ? 'selected' : '' ?>>Published — tampil di publik</option></select><label class="check-label"><input type="checkbox" name="unggulan" value="1" <?= $data['unggulan'] ? 'checked' : '' ?>> Jadikan berita unggulan</label></div>
    <div><label for="gambar">Unggah gambar utama baru</label><input id="gambar" type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small>JPG, PNG, atau WebP, maksimal 5 MB. Unggahan baru menjadi prioritas atas pilihan katalog.</small></div></div>
    <?php if ($data['gambar']): ?><div class="current-image"><p>Gambar saat ini</p><img src="<?= e(content_image($data['gambar'])) ?>" alt="Gambar berita saat ini" width="240"><label class="check-label"><input type="checkbox" name="hapus_gambar" value="1" <?= $data['hapus_gambar'] ? 'checked' : '' ?>> Lepaskan gambar dari berita ini</label></div><?php endif; ?>
    <label for="media_id">Atau gunakan gambar dari katalog media</label><select id="media_id" name="media_id"><option value="">Pertahankan gambar saat ini / tanpa gambar</option><?php foreach ($images as $image): if (!media_is_image($image)) { continue; } ?><option value="<?= $image['id'] ?>" <?= (string) $image['id'] === $data['media_id'] ? 'selected' : '' ?>>#<?= $image['id'] ?> — <?= e($image['nama_asli']) ?></option><?php endforeach; ?></select><small><?= $mediaTotal > 200 ? 'Pilihan menampilkan 200 gambar terbaru. ' : '' ?>Gambar yang dilepas atau diganti tetap tersedia di media. <a href="<?= e(base_url('panel/media/')) ?>">Buka katalog media</a>.</small>
    </div>
    <div class="form-actions"><button type="submit">Simpan berita</button><a href="<?= e(base_url('panel/berita/')) ?>">Batal</a></div>
</form>
<?php panel_end($user); ?>
