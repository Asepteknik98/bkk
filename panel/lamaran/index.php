<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/panel.php';
require_once APP_ROOT.'/app/helpers/validation.php';
$user = require_role('admin','staf_bkk');
require_once APP_ROOT.'/app/helpers/careers.php';
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
$where.=career_legacy_visibility($user);
$total = (int)panel_query('SELECT COUNT(*) FROM lamaran WHERE '.$where, $params)->fetchColumn();
$pages = max(1,(int)ceil($total/10)); $page = min($pages,positive_id($_GET['page'] ?? '1') ?? 1);
$rows = panel_query('SELECT id,nama,email,posisi,perusahaan,status,created_at FROM lamaran WHERE '.$where.' ORDER BY created_at DESC,id DESC LIMIT :limit OFFSET :offset', $params+['limit'=>10,'offset'=>($page-1)*10])->fetchAll();
$canNotify=user_can($user,'whatsapp')&&user_can($user,'lamaran')&&user_can($user,'tracer-study');
panel_start($user,'Lamaran Masuk','lamaran');
?>
<div class="content-management applications-inbox"><div class="page-actions"><h1><?= panel_icon('lamaran') ?> Lamaran Masuk</h1></div>
<form class="filter-form card" method="get"><label>Cari pelamar / lowongan<input type="search" name="q" maxlength="100" placeholder="Nama, email, posisi, atau perusahaan" value="<?= e($q) ?>"></label><label>Status<select name="status"><option value="">Semua status</option><option value="belum_dilihat" <?= $status==='belum_dilihat'?'selected':'' ?>>Belum dilihat</option><?php foreach ($statuses as $value=>$label): ?><option value="<?= $value ?>" <?= $status===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><button type="submit">Cari</button><a class="filter-reset" href="<?= e(base_url('panel/lamaran/')) ?>">Reset</a></form>
<p><?= $total ?> lamaran &middot; Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll admin-vertical-viewport panel-sheet" data-vertical-list id="applications-list" tabindex="0" role="region" aria-label="Daftar lamaran, gulir ke bawah atau geser ke samping"><table><thead><tr><th><span><?= panel_icon('akun') ?> Pelamar</span></th><th><span><?= panel_icon('lowongan') ?> Lowongan</span></th><th><span><?= panel_icon('calendar') ?> Dikirim (WIB)</span></th><th><span><?= panel_icon('lamaran') ?> Status</span></th><th><span><?= panel_icon('pengaturan') ?> Tindakan</span></th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><strong class="inbox-text"><?= e($row['nama']) ?></strong><small><?= e($row['email']) ?></small></td><td><span class="inbox-text"><?= e($row['posisi']) ?></span><small><?= e($row['perusahaan']) ?></small></td><td><?= e($row['created_at']) ?></td><td><span class="badge inbox-status inbox-status-<?= e($row['status']) ?>"><?= e($statuses[$row['status']]) ?></span></td><td><div class="application-actions"><a href="<?= e(base_url('panel/lamaran/detail.php?id='.$row['id'])) ?>" title="Lihat lamaran" aria-label="<?= e('Lihat lamaran '.$row['nama']) ?>"><?= panel_icon('view') ?></a><?php if($canNotify): ?><a class="application-notify" href="<?= e(base_url('panel/whatsapp/pelamar.php?id='.$row['id'])) ?>" title="Pemberitahuan WhatsApp" aria-label="<?= e('Siapkan pesan WhatsApp untuk '.$row['nama']) ?>"><?= panel_icon('whatsapp') ?></a><?php endif; ?><form method="post" action="<?= e(base_url('panel/lamaran/hapus.php')) ?>" data-confirm="Hapus lamaran dan berkas pelamar ini secara permanen? Lamaran yang terkait riwayat alumni tidak dapat dihapus."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="application-delete" type="submit" title="Hapus lamaran" aria-label="<?= e('Hapus lamaran '.$row['nama']) ?>"><?= panel_icon('trash') ?></button></form></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5">Belum ada lamaran yang sesuai.</td></tr><?php endif; ?></tbody></table></div>
<div class="admin-scroll-controls" data-scroll-target="applications-list" hidden><span>Gulir daftar</span><button type="button" data-scroll="-1" aria-controls="applications-list" aria-label="Gulir lamaran ke atas"><?= panel_icon('up') ?> Naik</button><button type="button" data-scroll="1" aria-controls="applications-list" aria-label="Gulir lamaran ke bawah"><?= panel_icon('down') ?> Turun</button></div>
<?php $pageStart=max(1,min($page-4,$pages-9));$pageEnd=min($pages,$pageStart+9); ?>
<nav class="pagination news-page-slider" aria-label="Halaman daftar lamaran">
<span class="news-page-summary"><?= $total?($page-1)*10+1:0 ?>&ndash;<?= min($total,$page*10) ?> dari <?= $total ?> lamaran</span>
<?php if($page>1): ?><a class="news-page-arrow" rel="prev" href="<?= e(base_url('panel/lamaran/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$page-1]))) ?>"><?= panel_icon('back') ?><span>Sebelumnya</span></a><?php else: ?><span class="news-page-arrow is-disabled" aria-disabled="true"><?= panel_icon('back') ?><span>Sebelumnya</span></span><?php endif; ?>
<div class="news-page-numbers">
<?php for($number=$pageStart;$number<=$pageEnd;$number++): ?><a class="news-page-number" href="<?= e(base_url('panel/lamaran/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$number]))) ?>" aria-label="Halaman <?= $number ?>" <?= $number===$page?'aria-current="page"':'' ?>><?= $number ?></a><?php endfor; ?>
</div>
<?php if($page<$pages): ?><a class="news-page-arrow" rel="next" href="<?= e(base_url('panel/lamaran/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$page+1]))) ?>"><span>Berikutnya</span><?= panel_icon('next') ?></a><?php else: ?><span class="news-page-arrow is-disabled" aria-disabled="true"><span>Berikutnya</span><?= panel_icon('next') ?></span><?php endif; ?>
</nav>

</div>
<script src="<?= e(asset_url('js/alumni-list.js')) ?>?v=<?= (int)filemtime(APP_ROOT.'/assets/js/alumni-list.js') ?>" defer></script>
<?php panel_end($user); ?>
