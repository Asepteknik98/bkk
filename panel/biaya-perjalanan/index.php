<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl-schedules.php';
$user=require_role('admin','staf_bkk');pkl_schedules_require_ready();
try{[$filters,$where,$params]=pkl_schedule_filters($_GET,true);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$from=' FROM pkl_business_trips a JOIN pkl_placements p ON p.id=a.placement_id JOIN siswa s ON s.id=p.siswa_id WHERE '.$where;
$summary=panel_query("SELECT COUNT(*) AS total,COALESCE(SUM(CASE WHEN a.sumber_biaya='sekolah' THEN a.nominal ELSE 0 END),0) AS sekolah,COALESCE(SUM(CASE WHEN a.sumber_biaya='pribadi' THEN a.nominal ELSE 0 END),0) AS pribadi".$from,$params)->fetch();
$total=(int)$summary['total'];$pages=max(1,(int)ceil($total/25));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT a.*,p.tanggal_mulai,p.tanggal_selesai,s.nama,s.nis'.$from.' ORDER BY a.tanggal DESC,a.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>25,'offset'=>($page-1)*25])->fetchAll();
$periods=pkl_period_options();$filterAction='panel/biaya-perjalanan/';$travel=true;
panel_start($user,'Perjalanan dinas & biaya','biaya-perjalanan');
?>
<div class="page-actions"><h1>Perjalanan dinas &amp; biaya</h1><a class="button" href="<?= e(base_url('panel/biaya-perjalanan/form.php?'.http_build_query(['placement_id'=>$filters['placement_id']]))) ?>">Catat perjalanan</a><a href="<?= e(base_url('panel/pkl/jadwal.php')) ?>">Jadwal PKL</a><a href="<?= e(base_url('panel/biaya-perjalanan/unduh.php?'.http_build_query($filters))) ?>">Unduh Excel biaya</a></div>
<p>Data internal. Perjalanan dapat dikaitkan ke monitoring dari penempatan yang sama atau dicatat tanpa kaitan monitoring. Total mengikuti filter, mencakup seluruh halaman.</p>
<?php require APP_ROOT.'/app/layouts/pkl-schedule-filter.php'; ?>
<p>Total sumber sekolah: <strong><?= e(pkl_rupiah((string)$summary['sekolah'])) ?></strong> · Pribadi: <strong><?= e(pkl_rupiah((string)$summary['pribadi'])) ?></strong></p>
<p><?= $total ?> perjalanan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Siswa / periode</th><th>Tanggal</th><th>Tujuan</th><th>Petugas</th><th>Biaya</th><th>Monitoring</th><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?= e($row['nama'].' / '.$row['nis']) ?><br><?= e($row['tanggal_mulai'].' – '.$row['tanggal_selesai']) ?></td><td><?= e($row['tanggal']) ?></td><td><?= e($row['tujuan']) ?></td><td><?= e($row['petugas']) ?></td><td><?= e(pkl_rupiah($row['nominal'])) ?><br>Sumber: <?= e(ucfirst($row['sumber_biaya'])) ?></td><td><?php if($row['monitoring_id']): ?><a href="<?= e(base_url('panel/pkl/jadwal-form.php?id='.$row['monitoring_id'])) ?>">Monitoring #<?= (int)$row['monitoring_id'] ?></a><?php else: ?>Tanpa kaitan monitoring<?php endif; ?></td><td><a href="<?= e(base_url('panel/biaya-perjalanan/form.php?id='.$row['id'])) ?>">Edit perjalanan</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7">Belum ada perjalanan untuk pilihan ini.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman perjalanan"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/biaya-perjalanan/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
<?php panel_end($user); ?>
