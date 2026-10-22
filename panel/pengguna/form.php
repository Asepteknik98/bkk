<?php
if (!isset($user, $data) || $user['role'] !== 'admin') { http_response_code(403); exit; }
panel_start($user, $id ? 'Edit Pengguna' : 'Tambah Pengguna', 'pengguna');
?>
<div class="account-editor-page"><div class="page-actions"><h1><?= panel_icon('pengguna') ?> <?= $id ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h1><a href="<?= e(base_url('panel/pengguna/')) ?>">&larr; Daftar pengguna</a></div>
<form class="editor-form card account-editor" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/pengguna/simpan.php')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?? '' ?>">
<div class="account-editor-section"><h2><?= panel_icon('akun') ?> Identitas pengguna</h2><?php account_photo_field($data); ?>
<?php foreach (['nama'=>'Nama lengkap','username'=>'Username','email'=>'Email'] as $key=>$label): ?><label for="<?= $key ?>"><?= $label ?></label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $key === 'email' ? 'email' : 'text' ?>" maxlength="<?= $key === 'username' ? 50 : ($key === 'nama' ? 150 : 191) ?>" value="<?= e($data[$key]) ?>" required><?php endforeach; ?>
<small>Username 3–50 huruf kecil/angka/titik/garis, diawali huruf atau angka. Email tetap dapat digunakan untuk login.</small>
</div><div class="account-editor-section account-access"><h2><?= panel_icon('pengaturan') ?> Akses akun</h2><label for="role">Role</label><select id="role" name="role"><?php foreach (['admin'=>'Admin','staf_bkk'=>'Staf BKK/Guru BKK'] as $value=>$label): ?><option value="<?= $value ?>" <?= $data['role'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select>
<?php if (!$id): ?>
<label for="password">Password (12–72 byte)</label><input id="password" type="password" name="password" autocomplete="new-password" maxlength="72" required>
<label for="confirmation">Ulangi password</label><input id="confirmation" type="password" name="confirmation" autocomplete="new-password" maxlength="72" required>
<?php endif; ?></div><div class="form-actions"><button type="submit">Simpan akun</button><a href="<?= e(base_url('panel/pengguna/')) ?>">Batal</a></div></form>
<?php if ($id): ?>
<section class="account-reset"><h2>Reset password</h2><p>Seluruh sesi akun ini akan dibatalkan. Sampaikan password baru melalui cara yang aman.</p>
<form class="editor-form card account-reset-form" method="post" action="<?= e(base_url('panel/pengguna/reset-password.php')) ?>" data-confirm="Reset password dan batalkan seluruh sesi akun ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
<label for="new-password">Password baru (12–72 byte)</label><input id="new-password" type="password" name="password" autocomplete="new-password" maxlength="72" required>
<label for="new-confirmation">Ulangi password baru</label><input id="new-confirmation" type="password" name="confirmation" autocomplete="new-password" maxlength="72" required><button type="submit">Reset password</button></form>
</section><?php endif; ?>
</div>
<?php panel_end($user); ?>
