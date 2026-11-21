<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
$user=require_role('admin','staf_bkk');pkl_require_ready();
require_permission($user,'pkl');
if(!pkl_status_ready()){
    http_response_code(503);panel_start($user,'Status siswa PKL','pkl');
    echo '<p class="notice">Fitur status dan riwayat belum aktif. Impor hanya database/updates/027_pkl_status_history.sql pada database yang digunakan website.</p>';
    panel_end($user);exit;
}
$studentId=positive_id($_GET['siswa_id']??'');
if(isset($_GET['siswa_id'])&&!$studentId){panel_not_found();}
$student=$studentId?panel_query('SELECT id,nama,kelas,jurusan FROM siswa WHERE id=:id',['id'=>$studentId])->fetch():false;
if($studentId&&!$student){panel_not_found();}
$errors=[];$newStatus=input_text($_POST,'status');$reason=input_text($_POST,'alasan');
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    try{
        $placementId=positive_id($_POST['placement_id']??'');$version=positive_id($_POST['version']??'');
        if(!$studentId||!$placementId||!$version){throw new InvalidArgumentException('Pilihan siswa atau versi penempatan tidak valid.');}
        pkl_change_status($studentId,$placementId,$version,$newStatus,$reason,(int)$user['id']);
        panel_message('Status diperbarui dan riwayat berhasil dicatat.','panel/pkl/status-siswa.php?siswa_id='.$studentId.'#pkl-status-history');
    }catch(Throwable $exception){$errors[]=pkl_error($exception);}
}

function pkl_status_pager(int $page,int $pages,array $params,string $anchor): void
{
    if($pages<=1){return;}
    $start=max(1,min($page-2,$pages-4));$end=min($pages,$start+4);
    echo '<nav class="pagination pkl-journal-pagination" aria-label="Halaman data PKL">';
    if($page>1){echo '<a rel="prev" href="'.e(base_url('panel/pkl/status-siswa.php?'.http_build_query($params+['page'=>$page-1]).$anchor)).'">'.panel_icon('back').' Sebelumnya</a>';}
    for($n=$start;$n<=$end;$n++){echo '<a aria-label="Halaman '.$n.'" '.($n===$page?'aria-current="page"':'').' href="'.e(base_url('panel/pkl/status-siswa.php?'.http_build_query($params+['page'=>$n]).$anchor)).'">'.$n.'</a>';}
    if($page<$pages){echo '<a rel="next" href="'.e(base_url('panel/pkl/status-siswa.php?'.http_build_query($params+['page'=>$page+1]).$anchor)).'">Berikutnya '.panel_icon('next').'</a>';}
    echo '</nav>';
}

if($student){
    $latest=panel_query('SELECT * FROM pkl_placements WHERE siswa_id=:id ORDER BY id DESC LIMIT 1',['id'=>$studentId])->fetch();
    $total=(int)panel_query('SELECT COUNT(*) FROM pkl_status_history h JOIN pkl_placements p ON p.id=h.placement_id WHERE p.siswa_id=:id',['id'=>$studentId])->fetchColumn();
    $pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
    $history=panel_query('SELECT h.*,p.tempat_pkl,p.tanggal_mulai,p.tanggal_selesai,u.nama AS pengelola FROM pkl_status_history h JOIN pkl_placements p ON p.id=h.placement_id LEFT JOIN users u ON u.id=h.changed_by WHERE p.siswa_id=:id ORDER BY h.id DESC LIMIT 10 OFFSET :offset',['id'=>$studentId,'offset'=>($page-1)*10])->fetchAll();
}else{
    foreach(['q','status'] as $key){if(isset($_GET[$key])&&!is_string($_GET[$key])){http_response_code(400);exit('Filter tidak valid.');}}
    $q=input_text($_GET,'q');$status=input_text($_GET,'status');
    if(!valid_text($q,100,false)||($status!==''&&!isset(pkl_status_options(true)[$status]))){http_response_code(400);exit('Filter tidak valid.');}
    // Largest placement ID means the latest placement recorded, not a guessed completion date.
    $from=" FROM siswa s LEFT JOIN pkl_placements p ON p.id=(SELECT MAX(x.id) FROM pkl_placements x WHERE x.siswa_id=s.id)";
    $counts=panel_query("SELECT COALESCE(p.status,'belum') AS status,COUNT(*) AS jumlah".$from." GROUP BY COALESCE(p.status,'belum')")->fetchAll();
    $where='1=1';$params=[];
    if($q!==''){$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$where.=" AND (s.nama LIKE :nama ESCAPE '!' OR s.kelas LIKE :kelas ESCAPE '!')";$params=['nama'=>$pattern,'kelas'=>$pattern];}
    if($status!==''){$where.=" AND COALESCE(p.status,'belum')=:status";$params['status']=$status;}
    $total=(int)panel_query('SELECT COUNT(*)'.$from.' WHERE '.$where,$params)->fetchColumn();$pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
    $rows=panel_query("SELECT s.id,s.nama,s.kelas,s.jurusan,p.id AS placement_id,p.tempat_pkl,COALESCE(p.status,'belum') AS status".$from.' WHERE '.$where.' ORDER BY s.nama,s.id LIMIT 10 OFFSET :offset',$params+['offset'=>($page-1)*10])->fetchAll();
}
panel_start($user,'Status siswa dan riwayat PKL','pkl');
?>
<div class="pkl-detail-workspace pkl-status-workspace">
<header class="page-actions"><div><h1><?= panel_icon('peserta') ?> <?= $student?'Status PKL: '.e($student['nama']):'Status siswa &amp; riwayat PKL' ?></h1><p>Status dicatat pengelola; tidak berubah otomatis berdasarkan tanggal.</p></div><nav class="pkl-detail-nav"><a href="<?= e(base_url($student?'panel/pkl/status-siswa.php':'panel/pkl/')) ?>"><?= panel_icon('back') ?> <?= $student?'Daftar status siswa':'Daftar penempatan' ?></a></nav></header>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if($student): ?>
<section class="pkl-status-current pkl-status-editor">
<div class="pkl-status-info"><h2><?= panel_icon('check-circle') ?> Penempatan saat ini <span class="pkl-life-badge status-<?= e($latest['status']??'belum') ?>"><?= e(pkl_status_label($latest['status']??'belum')) ?></span></h2>
<dl><div><dt>Kelas / jurusan</dt><dd><?= e($student['kelas'].' / '.$student['jurusan']) ?></dd></div><div><dt>Tempat PKL</dt><dd><?= e($latest['tempat_pkl']??'Belum ada penempatan') ?></dd></div><div><dt>Periode</dt><dd><?= $latest?e($latest['tanggal_mulai'].' - '.$latest['tanggal_selesai']):'Belum ditentukan' ?></dd></div></dl>
<div class="pkl-status-info-footer"><?php if($latest): ?><nav class="pkl-detail-nav" aria-label="Tindakan penempatan"><a href="<?= e(base_url('panel/pkl/detail.php?id='.$latest['id'])) ?>"><?= panel_icon('lamaran') ?> Jurnal &amp; laporan</a><a href="<?= e(base_url('panel/pkl/form.php?siswa_id='.$studentId)) ?>"><?= panel_icon('add') ?> Penempatan baru</a></nav><p>Untuk mengulang PKL, buat penempatan baru. Riwayat sebelumnya tetap tersimpan.</p><?php else: ?><p>Catat penempatan sebelum memperbarui status PKL. Data tempat/tahun lama belum dianggap terverifikasi.</p><a class="button" href="<?= e(base_url('panel/pkl/form.php?siswa_id='.$studentId)) ?>"><?= panel_icon('add') ?> Buat penempatan PKL</a><?php endif; ?></div></div>
<?php if($latest): ?><div class="pkl-status-update"><h2><?= panel_icon('edit') ?> Perbarui status</h2>
<form method="post" class="pkl-status-change"><?= csrf_field() ?><input type="hidden" name="placement_id" value="<?= (int)$latest['id'] ?>"><input type="hidden" name="version" value="<?= (int)$latest['version'] ?>">
<label for="life-status"><span><?= panel_icon('check-circle') ?> Status baru *</span><select id="life-status" name="status" required><?php if($latest['status']==='arsip'&&!$errors): ?><option value="" selected disabled>Pilih status setelah ditinjau</option><?php endif; ?><?php foreach(pkl_status_options() as $key=>$label): ?><option value="<?= $key ?>" <?= ($errors?$newStatus:$latest['status'])===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label for="life-reason"><span><?= panel_icon('message') ?> Alasan perubahan *</span><textarea id="life-reason" name="alasan" rows="2" maxlength="2000" required placeholder="Jelaskan alasan status diperbarui."><?= e($reason) ?></textarea></label>
<div class="pkl-status-save"><small>* Wajib diisi. Setiap perubahan masuk ke riwayat.</small><button type="submit"><?= panel_icon('save') ?> Simpan perubahan</button></div></form></div><?php endif; ?>
<p class="pkl-status-editor-note">Status mengikuti penempatan terakhir yang dicatat. Gagal PKL mencakup batal berangkat atau berhenti di tengah pelaksanaan; jelaskan pada alasan. Arsip lama perlu ditinjau.</p></section>
<section id="pkl-status-history" class="pkl-history-section"><div class="pkl-detail-section-heading"><h2><?= panel_icon('calendar') ?> Riwayat status PKL</h2><span><?= $total ?> catatan &middot; Halaman <?= $page ?> / <?= $pages ?></span></div>
<details class="pkl-history-help"><summary><?= panel_icon('message') ?> Tentang riwayat</summary><p>Seluruh penempatan siswa tetap tercatat. Snapshot awal menunjukkan status saat fitur diperbarui, bukan waktu perubahan sebelumnya.</p></details>
<div class="table-scroll pkl-detail-sheet pkl-life-history" data-vertical-list tabindex="0" role="region" aria-label="Riwayat status PKL"><table><thead><tr><th><?= panel_icon('calendar') ?> Waktu (WIB)</th><th><?= panel_icon('location') ?> Penempatan</th><th><?= panel_icon('reset') ?> Perubahan status</th><th><?= panel_icon('message') ?> Alasan &amp; pengelola</th></tr></thead><tbody>
<?php foreach($history as $row):$recorded=new DateTimeImmutable($row['recorded_at']); ?><tr>
<td><time datetime="<?= e($recorded->format('Y-m-d\TH:i:s').'+07:00') ?>"><strong><?= e($recorded->format('d-m-Y')) ?></strong><span><?= e($recorded->format('H:i:s')) ?></span></time><small class="pkl-history-source"><?= e(['baseline'=>'Snapshot awal','baru'=>'Penempatan baru','perubahan'=>'Perubahan status'][$row['sumber']]) ?></small></td>
<td><a class="pkl-history-place" href="<?= e(base_url('panel/pkl/detail.php?id='.$row['placement_id'])) ?>"><?= e($row['tempat_pkl']) ?></a><small><?= e($row['tanggal_mulai'].' - '.$row['tanggal_selesai']) ?></small></td>
<td><div class="pkl-history-transition"><?php if($row['status_sebelum']!==null): ?><span class="pkl-life-badge pkl-history-before"><?= e(pkl_status_label($row['status_sebelum'])) ?></span><span class="pkl-history-arrow" aria-label="menjadi"><?= panel_icon('next') ?></span><?php endif; ?><span class="pkl-life-badge status-<?= e($row['status_sesudah']) ?>"><?= e(pkl_status_label($row['status_sesudah'])) ?></span></div></td>
<td><p class="pkl-history-reason"><?= nl2br(e($row['alasan'])) ?></p><small class="pkl-history-actor"><?= panel_icon('akun') ?> <?= e($row['sumber']==='baseline'?'Sistem (snapshot migrasi)':($row['pengelola']??'Akun sudah tidak tersedia')) ?></small></td>
</tr><?php endforeach; ?><?php if(!$history): ?><tr><td colspan="4" class="empty-panel">Belum ada riwayat status. Catatan pertama dibuat saat penempatan disimpan.</td></tr><?php endif; ?></tbody></table></div>
<?php pkl_status_pager($page,$pages,['siswa_id'=>$studentId],'#pkl-status-history'); ?></section>
<?php else: ?>
<p class="pkl-week-help">Satu baris per siswa, mengikuti penempatan terakhir yang dicatat. Tanpa penempatan: Belum PKL. Riwayat tempat/tahun lama pada Data siswa tidak otomatis dianggap selesai. Angka ringkasan mencakup seluruh siswa.</p>
<nav class="pkl-status-summary" aria-label="Filter status siswa"><?php $countMap=array_column($counts,'jumlah','status');foreach(pkl_status_options(isset($countMap['arsip'])) as $key=>$label): ?><a class="pkl-life-badge status-<?= $key ?>" href="<?= e(base_url('panel/pkl/status-siswa.php?'.http_build_query(['status'=>$key,'q'=>$q]))) ?>" <?= $status===$key?'aria-current="page"':'' ?>><?= e($label) ?> <strong><?= (int)($countMap[$key]??0) ?></strong></a><?php endforeach; ?></nav>
<form class="filter-form card" method="get"><label for="life-q">Cari nama / kelas<input id="life-q" name="q" maxlength="100" value="<?= e($q) ?>"></label><label for="life-filter">Status<select id="life-filter" name="status"><option value="">Semua status</option><?php foreach(pkl_status_options(isset($countMap['arsip'])) as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button type="submit"><?= panel_icon('search') ?> Cari</button><a href="<?= e(base_url('panel/pkl/status-siswa.php')) ?>">Reset</a></form>
<div class="pkl-detail-section-heading"><h2><?= panel_icon('peserta') ?> Daftar siswa</h2><span><?= $total ?> siswa &middot; Halaman <?= $page ?> / <?= $pages ?></span></div>
<div class="table-scroll pkl-detail-sheet pkl-life-list" data-vertical-list tabindex="0" role="region" aria-label="Status PKL siswa"><table><thead><tr><th>Siswa / kelas</th><th>Penempatan terakhir</th><th>Status PKL</th><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?= e($row['nama']) ?></strong><small><?= e($row['kelas'].' / '.$row['jurusan']) ?></small></td><td><?= e($row['tempat_pkl']??'Belum ada penempatan') ?></td><td><span class="pkl-life-badge status-<?= e($row['status']) ?>"><?= e(pkl_status_label($row['status'])) ?></span></td><td><a class="pkl-life-view" href="<?= e(base_url('panel/pkl/status-siswa.php?siswa_id='.$row['id'])) ?>"><?= panel_icon('lamaran') ?> Status &amp; riwayat</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="4">Tidak ada siswa sesuai filter.</td></tr><?php endif; ?></tbody></table></div>
<?php pkl_status_pager($page,$pages,['q'=>$q,'status'=>$status],''); ?>
<?php endif; ?></div>
<?php panel_end($user); ?>
