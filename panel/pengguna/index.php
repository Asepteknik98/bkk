<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user = require_role('admin');
$accounts = panel_query('SELECT id,nama,username,email,role,aktif FROM users ORDER BY id')->fetchAll();
panel_start($user, 'Pengguna', 'pengguna');
?>
<div class="page-actions"><h1>Pengguna</h1><a class="button" href="<?= e(base_url('panel/pengguna/tambah.php')) ?>">Tambah akun</a></div>
<p>Akun nonaktif kehilangan akses panel. Admin aktif terakhir harus dipertahankan.</p>
<div class="table-scroll"><table><thead><tr><th>Nama / username</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($accounts as $account): ?><tr><td><?= e($account['nama']) ?><br><?= e($account['username']) ?></td><td><?= e($account['email']) ?></td><td><?= e($account['role']) ?></td><td><?= $account['aktif'] ? 'Aktif' : 'Nonaktif' ?></td><td><a href="<?= e(base_url('panel/pengguna/edit.php?id=' . $account['id'])) ?>">Edit / reset password</a><form method="post" action="<?= e(base_url('panel/pengguna/nonaktifkan.php')) ?>" data-confirm="Ubah status akun ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $account['id'] ?>"><input type="hidden" name="aktif" value="<?= $account['aktif'] ? '0' : '1' ?>"><button type="submit"><?= $account['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php panel_end($user); ?>
