<?php
if (!isset($user, $data) || $user['role'] !== 'admin') { http_response_code(403); exit; }
panel_start($user, $id ? 'Edit Pengguna' : 'Tambah Pengguna', 'pengguna');
?>
<div class="account-editor-page"><div class="page-actions"><h1><?= panel_icon('pengguna') ?> <?= $id ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h1><a href="<?= e(base_url('panel/pengguna/')) ?>"><?= panel_icon('back') ?> Daftar pengguna</a></div>
<form class="editor-form card account-editor" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/pengguna/simpan.php')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?? '' ?>">
<div class="account-photo-column"><h2><?= panel_icon('akun') ?> Foto pengguna</h2><?php account_photo_field($data); ?></div>
<div class="account-compact-fields"><h2><?= panel_icon('pengguna') ?> Identitas &amp; akses</h2><div class="account-field-grid">
<?php foreach (['nama'=>'Nama lengkap','username'=>'Username','email'=>'Email'] as $key=>$label): ?><div class="account-field"><label for="<?= $key ?>"><?= panel_icon(['nama'=>'akun','username'=>'identity','email'=>'mail'][$key]) ?> <?= e($label) ?></label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $key === 'email' ? 'email' : 'text' ?>" maxlength="<?= $key === 'username' ? 50 : ($key === 'nama' ? 150 : 191) ?>" value="<?= e($data[$key]) ?>" required></div><?php endforeach; ?>
<div class="account-field"><label for="role"><?= panel_icon('lock') ?> Akses akun</label><select id="role" name="role"><?php foreach (['admin'=>'Admin','staf_bkk'=>'Pengguna'] as $value=>$label): ?><option value="<?= $value ?>" <?= $data['role'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
<?php if (!$id): ?><div class="account-field"><label for="password"><?= panel_icon('key') ?> Password (12&ndash;72 byte)</label><input id="password" type="password" name="password" autocomplete="new-password" maxlength="72" required></div><div class="account-field"><label for="confirmation"><?= panel_icon('check-circle') ?> Ulangi password</label><input id="confirmation" type="password" name="confirmation" autocomplete="new-password" maxlength="72" required></div><?php endif; ?>
</div><small>Username 3&ndash;50 huruf kecil, angka, titik, atau garis; diawali huruf atau angka. Email juga bisa dipakai untuk login.</small></div>
<div class="form-actions"><button type="submit"><?= panel_icon('save') ?> Simpan akun</button><a href="<?= e(base_url('panel/pengguna/')) ?>"><?= panel_icon('close') ?> Batal</a></div></form>
<?php if ($id): ?>
<details class="account-reset"><summary><?= panel_icon('key') ?> Reset password</summary><p>Seluruh sesi akun ini akan dibatalkan. Sampaikan password baru melalui cara yang aman.</p>
<form class="editor-form card account-reset-form" method="post" action="<?= e(base_url('panel/pengguna/reset-password.php')) ?>" data-confirm="Reset password dan batalkan seluruh sesi akun ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
<label for="new-password">Password baru (12–72 byte)</label><input id="new-password" type="password" name="password" autocomplete="new-password" maxlength="72" required>
<label for="new-confirmation"><?= panel_icon('check-circle') ?> Ulangi password baru</label><input id="new-confirmation" type="password" name="confirmation" autocomplete="new-password" maxlength="72" required><button type="submit"><?= panel_icon('key') ?> Reset password</button></form>
</details><?php endif; ?>
</div>
<?php panel_end($user); ?>
