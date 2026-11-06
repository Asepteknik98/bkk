<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl-schedules.php';
$user=require_role('admin','staf_bkk');pkl_schedules_require_ready();
try{[$filters,$where,$params]=pkl_schedule_filters($_GET);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$from=' FROM pkl_schedules a JOIN pkl_placements p ON p.id=a.placement_id JOIN siswa s ON s.id=p.siswa_id WHERE '.$where;
$total=(int)panel_query('SELECT COUNT(*)'.$from,$params)->fetchColumn();$pages=max(1,(int)ceil($total/25));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT a.*,p.tanggal_mulai,p.tanggal_selesai,s.nama,s.nis'.$from.' ORDER BY a.tanggal,a.jam,a.id LIMIT :limit OFFSET :offset',$params+['limit'=>25,'offset'=>($page-1)*25])->fetchAll();
$periods=pkl_period_options();$filterAction='panel/pkl/jadwal.php';$travel=false;
panel_start($user,'Jadwal PKL','pkl');
?>
<div class="page-actions"><h1>Jadwal PKL</h1><a href="<?= e(base_url('panel/pkl/')) ?>">Penempatan PKL</a><a href="<?= e(base_url('panel/pkl/jadwal-unduh.php?'.http_build_query($filters))) ?>">Unduh jadwal Excel</a></div>
<div class="page-actions"><?php foreach(pkl_schedule_types() as $key=>$label): ?><a class="button" href="<?= e(base_url('panel/pkl/jadwal-form.php?'.http_build_query(['jenis'=>$key,'placement_id'=>$filters['placement_id']]))) ?>">Tambah <?= e($label) ?></a><?php endforeach; ?>
<?php if(user_can($user,'biaya-perjalanan')): ?><a href="<?= e(base_url('panel/biaya-perjalanan/?'.http_build_query(['placement_id'=>$filters['placement_id']]))) ?>">Perjalanan dinas &amp; biaya</a><?php endif; ?></div>
<p>Jadwal terkait siswa dan periode penempatannya. Monitoring dikelompokkan menurut minggu kalender Senin–Minggu. Jam memakai WIB. Pertemuan dan sidang dapat dijadwalkan di luar tanggal praktik.</p>
<?php require APP_ROOT.'/app/layouts/pkl-schedule-filter.php'; ?>
<p><?= $total ?> jadwal · Halaman <?= $page ?> dari <?= $pages ?></p><div class="table-scroll"><table><thead><tr><th>Jenis / siswa</th><th>Periode PKL</th><th>Jadwal (WIB)</th><th>Guru / petugas</th><th>Tujuan &amp; alamat</th><th>Status / hasil</th><th>Tindakan</th></tr></thead><tbody>
<?php $lastWeek='';foreach($rows as $row):$date=new DateTimeImmutable($row['tanggal']);$monday=$date->modify('monday this week');$week=$monday->format('Y-m-d');if($lastWeek!==$week):$lastWeek=$week; ?>
<tr><th colspan="7">Minggu <?= e($date->format('o-\WW')) ?> · <?= e($week.' – '.$monday->modify('+6 days')->format('Y-m-d')) ?></th></tr><?php endif; ?>
<tr><td><?= e(pkl_schedule_types()[$row['jenis']]) ?><br><strong><?= e($row['nama']) ?></strong><br><?= e($row['nis']) ?><?php if($row['orang_tua_wali']!==''): ?><p>Undangan: <?= e($row['orang_tua_wali']) ?></p><?php endif; ?></td><td><?= e($row['tanggal_mulai'].' – '.$row['tanggal_selesai']) ?></td><td><?= e($row['tanggal'].' '.substr($row['jam'],0,5)) ?></td><td><?= e($row['petugas']) ?></td><td><strong><?= e($row['tujuan']) ?></strong><p><?= nl2br(e($row['alamat_lengkap'])) ?></p><p>Agenda: <?= nl2br(e($row['agenda'])) ?></p></td><td><?= e(pkl_schedule_statuses()[$row['status']]) ?><?php if($row['jenis']==='monitoring'): ?><p>Hasil: <?= nl2br(e($row['hasil_monitoring']?:'Belum dicatat')) ?></p><p>Tindak lanjut: <?= nl2br(e($row['tindak_lanjut']?:'Belum dicatat')) ?></p><?php endif; ?></td><td><a href="<?= e(base_url('panel/pkl/jadwal-form.php?id='.$row['id'])) ?>">Edit jadwal / hasil</a><br><a href="<?= e(base_url('panel/pkl/detail.php?id='.$row['placement_id'])) ?>">Detail PKL</a><?php if($row['jenis']==='monitoring'&&user_can($user,'biaya-perjalanan')): ?><br><a href="<?= e(base_url('panel/biaya-perjalanan/form.php?placement_id='.$row['placement_id'].'&monitoring_id='.$row['id'])) ?>">Catat perjalanan</a><?php endif; ?></td></tr>
<?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7">Belum ada jadwal untuk pilihan ini.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman jadwal"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/pkl/jadwal.php?'.http_build_query($filters+['page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
<?php panel_end($user); ?>
