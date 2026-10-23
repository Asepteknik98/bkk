<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/attendance.php';
$user=require_role('admin','staf_bkk');
try{[$filters,$where,$params]=attendance_filters($_GET);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$from=' FROM attendance_records r JOIN attendance_events e ON e.id=r.event_id JOIN siswa s ON s.id=r.siswa_id WHERE '.$where;
$total=(int)panel_query('SELECT COUNT(DISTINCT r.siswa_id)'.$from,$params)->fetchColumn();
$pages=max(1,(int)ceil($total/25));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query("SELECT s.id,s.nis,s.nama,s.kelas,COUNT(*) AS kegiatan,SUM(r.status='hadir') AS hadir,SUM(r.status='izin') AS izin,SUM(r.status='sakit') AS sakit,SUM(r.status='alpa') AS alpa,SUM(r.status IS NULL) AS belum".$from.' GROUP BY s.id,s.nis,s.nama,s.kelas ORDER BY s.nama,s.id LIMIT :limit OFFSET :offset',$params+['limit'=>25,'offset'=>($page-1)*25])->fetchAll();
panel_start($user,'Rekap absensi siswa','absensi');
?>
<div class="page-actions"><h1>Rekap absensi siswa</h1><a href="<?= e(base_url('panel/absensi/?'.http_build_query($filters))) ?>">Daftar kegiatan</a></div>
<p>Rekap menghitung kegiatan yang diikuti setiap siswa sesuai filter. Belum diisi dihitung terpisah dari Alpa. Identitas pada rekap mengikuti Data Siswa terbaru; identitas dalam setiap kegiatan mengikuti riwayat peserta saat kegiatan dibuat.</p>
<?php attendance_filter_form($filters,'panel/absensi/rekap.php'); ?>
<p><?= $total ?> siswa · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Kegiatan</th><?php foreach(attendance_statuses() as $label):?><th><?= e($label) ?></th><?php endforeach;?><th>Belum diisi</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?= e($row['nis']) ?></td><td><?= e($row['nama']) ?></td><td><?= e($row['kelas']) ?></td><td><?= $row['kegiatan'] ?></td><?php foreach(attendance_statuses() as $key=>$label):?><td><?= (int)$row[$key] ?></td><?php endforeach;?><td><?= $row['belum'] ?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="9" class="empty-panel">Belum ada peserta kegiatan yang sesuai.</td></tr><?php endif;?></tbody></table></div>
<nav class="pagination" aria-label="Halaman rekap"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/absensi/rekap.php?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav>
<?php panel_end($user); ?>
