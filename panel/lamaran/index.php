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
if ($q !== '') {
    $pattern = '%'.str_replace(['!','%','_'], ['!!','!%','!_'], $q).'%';
    $where .= " AND (nama LIKE :nama ESCAPE '!' OR email LIKE :email ESCAPE '!' OR posisi LIKE :posisi ESCAPE '!' OR perusahaan LIKE :perusahaan ESCAPE '!')";
    foreach (['nama','email','posisi','perusahaan'] as $field) { $params[$field] = $pattern; }
}
$total = (int)panel_query('SELECT COUNT(*) FROM lamaran WHERE '.$where, $params)->fetchColumn();
$pages = max(1,(int)ceil($total/10)); $page = min($pages,positive_id($_GET['page'] ?? '1') ?? 1);
$rows = panel_query('SELECT id,nama,email,posisi,perusahaan,status,created_at FROM lamaran WHERE '.$where.' ORDER BY created_at DESC,id DESC LIMIT :limit OFFSET :offset', $params+['limit'=>10,'offset'=>($page-1)*10])->fetchAll();
panel_start($user,'Lamaran Masuk','lamaran');
?>
<div class="content-management applications-inbox"><div class="page-actions"><h1>Lamaran Masuk</h1></div>
<form class="filter-form card" method="get"><label>Cari pelamar / lowongan<input type="search" name="q" maxlength="100" placeholder="Nama, email, posisi, atau perusahaan" value="<?= e($q) ?>"></label><label>Status<select name="status"><option value="">Semua status</option><?php foreach ($statuses as $value=>$label): ?><option value="<?= $value ?>" <?= $status===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><button type="submit">Cari</button><a class="filter-reset" href="<?= e(base_url('panel/lamaran/')) ?>">Reset</a></form>
<p><?= $total ?> lamaran &middot; Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll" tabindex="0" role="region" aria-label="Daftar lamaran, geser ke samping pada layar kecil"><table><thead><tr><th>Pelamar</th><th>Lowongan</th><th>Dikirim (WIB)</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><strong class="inbox-text"><?= e($row['nama']) ?></strong><small><?= e($row['email']) ?></small></td><td><span class="inbox-text"><?= e($row['posisi']) ?></span><small><?= e($row['perusahaan']) ?></small></td><td><?= e($row['created_at']) ?></td><td><span class="badge"><?= e($statuses[$row['status']]) ?></span></td><td><a href="<?= e(base_url('panel/lamaran/detail.php?id='.$row['id'])) ?>">Buka lamaran</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5">Belum ada lamaran yang sesuai.</td></tr><?php endif; ?></tbody></table></div>
<?php if ($pages>1): ?><nav class="pagination" aria-label="Halaman lamaran"><?php foreach (['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target): if ($target<1 || $target>$pages) { continue; } ?><a href="<?= e(base_url('panel/lamaran/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav><?php endif; ?>
</div>
<?php panel_end($user); ?>
