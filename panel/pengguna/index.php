<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user = require_role('admin');
$accounts = panel_query('SELECT id,nama,username,email,foto_profil,role,aktif FROM users ORDER BY id')->fetchAll();
panel_start($user, 'Pengguna', 'pengguna');
?>
<div class="users-page"><div class="page-actions"><div><p class="eyebrow">Akses pengelola</p><h1><?= panel_icon('pengguna') ?> Pengguna <span class="badge"><?= count($accounts) ?> akun</span></h1></div><a class="button" href="<?= e(base_url('panel/pengguna/tambah.php')) ?>">+ Tambah akun</a></div>
<p class="users-note">Akun nonaktif kehilangan akses panel. Admin aktif terakhir harus dipertahankan.</p>
<div class="table-scroll"><table><thead><tr><th>Nama / username</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($accounts as $account): ?><tr><td><div class="user-identity"><?php $photo=account_photo_url($account); if($photo): ?><img class="user-profile-photo" src="<?= e($photo) ?>" alt="" width="34" height="34"><?php else: ?><span class="user-initial" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($account['nama'],0,1))) ?></span><?php endif; ?><div><strong><?= e($account['nama']) ?></strong><small>@<?= e($account['username']) ?><?= (int)$account['id']===(int)$user['id']?' ? Anda':'' ?></small></div></div></td><td><?= e($account['email']) ?></td><td><span class="user-role"><?= panel_icon($account['role']==='admin'?'pengaturan':'akun') ?> <?= e(['admin'=>'Admin','staf_bkk'=>'Pengguna'][$account['role']]??$account['role']) ?></span></td><td><span class="user-status <?= $account['aktif']?'is-active':'is-inactive' ?>"><?= $account['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td><td><div class="row-actions"><a class="button secondary" href="<?= e(base_url('panel/pengguna/edit.php?id=' . $account['id'])) ?>">Edit / reset password</a><form method="post" action="<?= e(base_url('panel/pengguna/nonaktifkan.php')) ?>" data-confirm="Ubah status akun ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $account['id'] ?>"><input type="hidden" name="aktif" value="<?= $account['aktif'] ? '0' : '1' ?>"><button type="submit"><?= $account['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form></div></td></tr><?php endforeach; ?>
</tbody></table></div>
</div>
<?php panel_end($user); ?>
