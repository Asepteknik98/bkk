<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/panel.php';
require_once APP_ROOT.'/app/helpers/validation.php';
$user = require_role('admin','staf_bkk');
$statuses = ['baru'=>'Baru','diproses'=>'Diproses','diterima'=>'Diterima','ditolak'=>'Ditolak'];
$status = input_text($_GET, 'status');
$q = input_text($_GET, 'q');
if (!valid_text($q, 100, false)) { $q = ''; }
$params = []; $where = '1=1';
if (isset($statuses[$status])) { $where = 'status=:status'; $params['status']=$status; }
if ($status==='belum_dilihat') { $where='viewed_at IS NULL'; }
if ($q !== '') {
    $pattern = '%'.str_replace(['!','%','_'], ['!!','!%','!_'], $q).'%';
    $where .= " AND (nama LIKE :nama ESCAPE '!' OR email LIKE :email ESCAPE '!' OR posisi LIKE :posisi ESCAPE '!' OR perusahaan LIKE :perusahaan ESCAPE '!')";
    foreach (['nama','email','posisi','perusahaan'] as $field) { $params[$field] = $pattern; }
}
$total = (int)panel_query('SELECT COUNT(*) FROM lamaran WHERE '.$where, $params)->fetchColumn();
$pages = max(1,(int)ceil($total/25)); $page = min($pages,positive_id($_GET['page'] ?? '1') ?? 1);
$rows = panel_query('SELECT id,nama,email,posisi,perusahaan,status,created_at FROM lamaran WHERE '.$where.' ORDER BY created_at DESC,id DESC LIMIT :limit OFFSET :offset', $params+['limit'=>25,'offset'=>($page-1)*25])->fetchAll();
panel_start($user,'Lamaran Masuk','lamaran');
?>
<div class="content-management applications-inbox"><div class="page-actions"><h1><?= panel_icon('lamaran') ?> Lamaran Masuk</h1></div>
<form class="filter-form card" method="get"><label>Cari pelamar / lowongan<input type="search" name="q" maxlength="100" placeholder="Nama, email, posisi, atau perusahaan" value="<?= e($q) ?>"></label><label>Status<select name="status"><option value="">Semua status</option><option value="belum_dilihat" <?= $status==='belum_dilihat'?'selected':'' ?>>Belum dilihat</option><?php foreach ($statuses as $value=>$label): ?><option value="<?= $value ?>" <?= $status===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><button type="submit">Cari</button><a class="filter-reset" href="<?= e(base_url('panel/lamaran/')) ?>">Reset</a></form>
<p><?= $total ?> lamaran &middot; Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll admin-vertical-viewport" data-vertical-list id="applications-list" tabindex="0" role="region" aria-label="Daftar lamaran, gulir ke bawah atau geser ke samping"><table><thead><tr><th><?= panel_icon('akun') ?> Pelamar</th><th><?= panel_icon('lowongan') ?> Lowongan</th><th><?= panel_icon('calendar') ?> Dikirim (WIB)</th><th><?= panel_icon('lamaran') ?> Status</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><strong class="inbox-text"><?= e($row['nama']) ?></strong><small><?= e($row['email']) ?></small></td><td><span class="inbox-text"><?= e($row['posisi']) ?></span><small><?= e($row['perusahaan']) ?></small></td><td><?= e($row['created_at']) ?></td><td><span class="badge"><?= e($statuses[$row['status']]) ?></span></td><td><a href="<?= e(base_url('panel/lamaran/detail.php?id='.$row['id'])) ?>"><?= panel_icon('view') ?> Lihat</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5">Belum ada lamaran yang sesuai.</td></tr><?php endif; ?></tbody></table></div>
<div class="admin-scroll-controls" data-scroll-target="applications-list" hidden><span>Gulir daftar</span><button type="button" data-scroll="-1" aria-controls="applications-list" aria-label="Gulir lamaran ke atas"><?= panel_icon('up') ?> Naik</button><button type="button" data-scroll="1" aria-controls="applications-list" aria-label="Gulir lamaran ke bawah"><?= panel_icon('down') ?> Turun</button></div>
<?php if($pages>1): $pageNumbers=array_unique(array_merge([1,$pages],range(max(1,$page-2),min($pages,$page+2))));sort($pageNumbers); ?>
<nav class="admin-pagination" aria-label="Halaman lamaran">
<?php if($page>1):?><a href="<?= e(base_url('panel/lamaran/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$page-1]))) ?>"><?= panel_icon('back') ?> Sebelumnya</a><?php endif;?>
<?php $last=0;foreach($pageNumbers as $target):if($last&&$target>$last+1):?><span aria-hidden="true">...</span><?php endif;?><a href="<?= e(base_url('panel/lamaran/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$target]))) ?>" <?= $target===$page?'aria-current="page"':'' ?> aria-label="Halaman <?= $target ?>"><?= $target ?></a><?php $last=$target;endforeach;?>
<?php if($page<$pages):?><a href="<?= e(base_url('panel/lamaran/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$page+1]))) ?>">Berikutnya <?= panel_icon('next') ?></a><?php endif;?></nav>
<form class="admin-page-jump" method="get"><input type="hidden" name="q" value="<?= e($q) ?>"><input type="hidden" name="status" value="<?= e($status) ?>"><label for="application-page">Ke halaman</label><input id="application-page" name="page" type="number" min="1" max="<?= $pages ?>" value="<?= $page ?>" required><button type="submit">Buka</button><span><?= $pages ?> halaman &middot; 25 data per halaman</span></form>
<?php endif;?></div>
<script src="<?= e(asset_url('js/alumni-list.js')) ?>?v=<?= (int)filemtime(APP_ROOT.'/assets/js/alumni-list.js') ?>" defer></script>
<?php panel_end($user); ?>
