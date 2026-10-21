<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/news.php';
$user = require_role('admin', 'staf_bkk');
require_post_csrf();
$rawId = $_POST['id'] ?? '';
$id = $rawId === '' ? null : positive_id($rawId);
if ($rawId !== '' && $id === null) { panel_not_found(); }
if ($id && !panel_query('SELECT id FROM berita WHERE id=:id', ['id' => $id])->fetch()) { panel_not_found(); }
$data = news_input($_POST); $errors = news_errors($data); $upload = null;
if (!$errors) {
    try {
        if (isset($_FILES['gambar']) && !is_array($_FILES['gambar'])) { throw new InvalidArgumentException('Data gambar tidak valid.'); }
        $upload = store_upload($_FILES['gambar'] ?? null);
        $savedId = save_news($data, $id, (int) $user['id'], $upload);
        unset($_SESSION['news_form']);
        panel_message('Berita berhasil disimpan.', 'panel/berita/edit.php?id=' . $savedId);
    } catch (InvalidArgumentException $exception) {
        $errors[] = $exception->getMessage();
    } catch (Throwable $exception) {
        error_log('Gagal menyimpan berita: ' . get_class($exception) . ' ' . $exception->getCode());
        $errors[] = 'Berita belum dapat disimpan. Periksa koneksi database dan izin direktori unggahan, lalu coba kembali.';
    }
}
discard_upload($upload);
$_SESSION['news_form'] = ['id' => $id, 'data' => $data, 'errors' => $errors];
redirect($id ? 'panel/berita/edit.php?id=' . $id : 'panel/berita/tambah.php', 303);
