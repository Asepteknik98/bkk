<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/student-portal.php';
require_once APP_ROOT.'/app/helpers/attendance.php';
[$user,$student]=require_student();$params=['student'=>(int)$student['id']];
$total=(int)panel_query('SELECT COUNT(*) FROM attendance_records WHERE siswa_id=:student',$params)->fetchColumn();$pages=max(1,(int)ceil($total/25));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT e.judul,e.tanggal,e.angkatan,e.kelas_industri,r.status,r.keterangan,r.updated_at FROM attendance_records r JOIN attendance_events e ON e.id=r.event_id WHERE r.siswa_id=:student ORDER BY e.tanggal DESC,e.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>25,'offset'=>($page-1)*25])->fetchAll();
student_start($user,$student,'Absensi saya','absensi.php');
?>
<p>Riwayat ini hanya memuat absensi Anda, termasuk kegiatan dari kelompok sebelumnya. Perubahan status dilakukan pengelola.</p><p><?= $total ?> kegiatan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="surface table-responsive"><table class="table"><thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Angkatan</th><th>Kelas industri</th><th>Status</th><th>Keterangan</th><th>Diperbarui</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?= e($row['judul']) ?></td><td><?= e($row['tanggal']) ?></td><td><?= e((string)$row['angkatan']) ?></td><td><?= e($row['kelas_industri']) ?></td><td><?= e($row['status']===null?'Belum diisi':attendance_statuses()[$row['status']]) ?></td><td><?= e($row['keterangan']) ?></td><td><?= e($row['updated_at']??'') ?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="7">Belum ada riwayat absensi.</td></tr><?php endif;?></tbody></table></div>
<?php student_pagination('absensi.php',$page,$pages);student_end(); ?>
