<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/news.php';
$user = require_role('admin', 'staf_bkk');
$posting = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
if ($posting) { require_post_csrf(); }
$id = positive_id(($posting ? $_POST : $_GET)['id'] ?? null);
$media = $id ? panel_query('SELECT * FROM media WHERE id=:id', ['id' => $id])->fetch() : false;
if (!$media) { panel_not_found(); }
$extension = pathinfo($media['nama_asli'], PATHINFO_EXTENSION);
$name = $posting ? input_text($_POST, 'nama') : pathinfo($media['nama_asli'], PATHINFO_FILENAME);
$error = '';
if ($posting) {
    // Only the display name changes; the stored random path and extension stay intact.
    if (!valid_text($name, 240) || !valid_text($name . '.' . $extension, 255)
        || preg_match('~[\\\\/\x00-\x1f\x7f<>:"|?*]~', $name)
        || str_contains($name, '..') || str_ends_with($name, '.')
        || preg_match('/\.(?:php\d*|phtml|pht|phar|cgi|pl|exe|com|bat|cmd|ps1|js|html?|shtml|svg)(?:\.|$)/i', $name)) {
        $error = 'Isi nama berkas yang valid, maksimal 240 karakter, tanpa path atau ekstensi tambahan berbahaya.';
    } else {
        try {
            panel_query('UPDATE media SET nama_asli=:nama WHERE id=:id', ['nama' => $name . '.' . $extension, 'id' => $id]);
            panel_message('Nama berkas berhasil diperbarui.', 'panel/media/');
        } catch (Throwable $exception) {
            error_log('Edit media gagal: ' . get_class($exception) . ' ' . $exception->getCode());
            $error = 'Perubahan belum dapat disimpan. Silakan coba kembali.';
        }
    }
}
panel_start($user, 'Edit Media', 'media');
?>
<div class="media-catalog media-edit-page">
<div class="page-actions"><h1><?= panel_icon('edit') ?> Edit Media</h1><a href="<?= e(base_url('panel/media/')) ?>"><?= panel_icon('back') ?> Katalog media</a></div>
<?php if ($error !== ''): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endif; ?>
<form class="editor-form card" method="post" action="<?= e(base_url('panel/media/edit.php')) ?>">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $id ?>">
<label for="nama">Nama berkas (tanpa ekstensi)</label>
<input id="nama" name="nama" value="<?= e($name) ?>" maxlength="240" required aria-describedby="media-name-help">
<small id="media-name-help">Ekstensi .<?= e($extension) ?> tetap. Mengubah nama tidak mengubah gambar, tautan, atau pemakaiannya pada konten.</small>
<div class="form-actions"><button type="submit"><?= panel_icon('save') ?> Simpan nama</button><a href="<?= e(base_url('panel/media/')) ?>">Batal</a></div>
</form></div>
<?php panel_end($user); ?>
