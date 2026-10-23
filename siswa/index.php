<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/student-portal.php';
[$user,$student]=require_student();
$group=student_group($student);
$events=(int)panel_query("SELECT COUNT(*) FROM attendance_events WHERE angkatan=:angkatan AND kelas_industri=:industri AND kelas_industri<>''",$group)->fetchColumn();
$documents=(int)panel_query("SELECT COUNT(*) FROM learning_documents WHERE angkatan=:angkatan AND kelas_industri=:industri AND kelas_industri<>''",$group)->fetchColumn();
$attendance=panel_query('SELECT status,COUNT(*) AS jumlah FROM attendance_records WHERE siswa_id=:id GROUP BY status',['id'=>(int)$student['id']])->fetchAll();
$counts=['hadir'=>0,'izin'=>0,'sakit'=>0,'alpa'=>0,'belum'=>0];foreach($attendance as $row){$counts[$row['status']??'belum']=(int)$row['jumlah'];}
student_start($user,$student,'Beranda siswa','');
?>
<div class="row g-4"><section class="col-12 col-md-6"><div class="surface h-100"><h2>Kegiatan kelas</h2><p><?= $events ?> kegiatan untuk angkatan dan kelas industri Anda.</p><a class="button button-navy" href="<?= e(base_url('siswa/kegiatan.php')) ?>">Lihat kegiatan dan pretest</a></div></section><section class="col-12 col-md-6"><div class="surface h-100"><h2>Capaian pembelajaran</h2><p><?= $documents ?> dokumen tersedia untuk kelompok Anda.</p><a class="button button-navy" href="<?= e(base_url('siswa/capaian.php')) ?>">Lihat materi</a></div></section></div>
<section class="surface mt-4"><h2>Ringkasan absensi saya</h2><p>Hadir: <strong><?= $counts['hadir'] ?></strong> · Izin: <strong><?= $counts['izin'] ?></strong> · Sakit: <strong><?= $counts['sakit'] ?></strong> · Alpa: <strong><?= $counts['alpa'] ?></strong> · Belum diisi: <strong><?= $counts['belum'] ?></strong></p><a href="<?= e(base_url('siswa/absensi.php')) ?>">Lihat riwayat absensi</a><p>Absensi dicatat oleh admin/staf. Hubungi pengelola jika data perlu diperbaiki.</p></section>
<?php if($student['kelas_industri']===''):?><p class="surface mt-4">Kelas industri Anda belum ditentukan. Hubungi pengelola agar kegiatan dan materi yang sesuai dapat ditampilkan.</p><?php endif;?>
<?php student_end(); ?>
