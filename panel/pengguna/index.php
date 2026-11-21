<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user = require_role('admin');
$q=input_text($_GET,'q');
if(!valid_text($q,100,false)){http_response_code(400);exit('Pencarian tidak valid, maksimal 100 karakter.');}
$where='';$params=[];
if($q!==''){
    $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';
    $where=" WHERE nama LIKE :nama ESCAPE '!' OR username LIKE :username ESCAPE '!' OR email LIKE :email ESCAPE '!'";
    $params=['nama'=>$pattern,'username'=>$pattern,'email'=>$pattern];
}
$accounts=panel_query('SELECT id,nama,username,email,foto_profil,role,aktif FROM users'.$where.' ORDER BY id',$params)->fetchAll();
panel_start($user, 'Pengguna', 'pengguna');
?>
<div class="users-page users-list-page"><div class="page-actions"><div><h1><?= panel_icon('pengguna') ?> Pengguna <span class="badge"><?= count($accounts) ?> akun</span></h1></div><a class="button" href="<?= e(base_url('panel/pengguna/tambah.php')) ?>"><?= panel_icon('add') ?> Tambah akun</a></div>
<p class="users-note"><?= panel_icon('lock') ?> Akun nonaktif tidak dapat masuk. Admin aktif terakhir tetap dilindungi.</p>
<form class="users-search" method="get" role="search"><label for="users-search"><?= panel_icon('search') ?> Cari pengguna</label><div><input id="users-search" name="q" maxlength="100" value="<?= e($q) ?>" placeholder="Nama, username, atau email"><button type="submit"><?= panel_icon('search') ?> Cari</button><a href="<?= e(base_url('panel/pengguna/')) ?>"><?= panel_icon('reset') ?> Reset</a></div></form>
<div class="table-scroll panel-sheet users-sheet" tabindex="0" role="region" aria-label="Daftar akun pengelola, gulir untuk melihat data lainnya"><table><thead><tr><th><span><?= panel_icon('identity') ?> Nama / username</span></th><th><span><?= panel_icon('mail') ?> Email</span></th><th><span><?= panel_icon('lock') ?> Role</span></th><th><span><?= panel_icon('check-circle') ?> Status</span></th><th><span><?= panel_icon('pengaturan') ?> Tindakan</span></th></tr></thead><tbody>
<?php foreach ($accounts as $account): ?><tr><td><div class="user-identity"><?php $photo=account_photo_url($account); if($photo): ?><img class="user-profile-photo" src="<?= e($photo) ?>" alt="" width="34" height="34"><?php else: ?><span class="user-initial" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($account['nama'],0,1))) ?></span><?php endif; ?><div><strong><?= e($account['nama']) ?></strong><small>@<?= e($account['username']) ?><?= (int)$account['id']===(int)$user['id']?' (Anda)':'' ?></small></div></div></td><td><?= e($account['email']) ?></td><td><span class="user-role <?= $account['role']==='admin'?'is-admin':'is-staff' ?>"><?= panel_icon($account['role']==='admin'?'pengaturan':'akun') ?> <?= e(['admin'=>'Admin','staf_bkk'=>'Staf BKK'][$account['role']]??$account['role']) ?></span></td><td><span class="user-status <?= $account['aktif']?'is-active':'is-inactive' ?>"><?= panel_icon($account['aktif']?'check-circle':'close') ?> <?= $account['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td><td><div class="row-actions"><a class="button secondary" title="Edit profil, role, dan reset password" aria-label="<?= e('Edit akun '.$account['nama']) ?>" href="<?= e(base_url('panel/pengguna/edit.php?id=' . $account['id'])) ?>"><?= panel_icon('edit') ?> Edit akun</a><form method="post" action="<?= e(base_url('panel/pengguna/nonaktifkan.php')) ?>" data-confirm="<?= e(($account['aktif']?'Nonaktifkan':'Aktifkan').' akun '.$account['nama'].'?') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $account['id'] ?>"><input type="hidden" name="aktif" value="<?= $account['aktif'] ? '0' : '1' ?>"><button class="<?= $account['aktif']?'user-deactivate':'user-activate' ?>" type="submit" aria-label="<?= e(($account['aktif']?'Nonaktifkan':'Aktifkan').' akun '.$account['nama']) ?>"><?= panel_icon($account['aktif']?'lock':'check-circle') ?> <?= $account['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form></div></td></tr><?php endforeach; ?>
<?php if(!$accounts): ?><tr><td colspan="5"><?= $q!==''?'Pengguna tidak ditemukan.':'Belum ada akun pengguna.' ?></td></tr><?php endif; ?>
</tbody></table></div>
</div>
<?php panel_end($user); ?>
