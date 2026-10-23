<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/attendance.php';
$user=require_role('admin','staf_bkk');
if(!panel_query("SHOW TABLES LIKE 'attendance_events'")->fetchColumn()){
    panel_start($user,'Absensi kegiatan','absensi');echo '<p class="alert">Jalankan pembaruan database 011_attendance.sql terlebih dahulu.</p>';panel_end($user);exit;
}
try{[$filters,$where,$params]=attendance_filters($_GET);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$data=attendance_input(['tanggal'=>date('Y-m-d'),'angkatan'=>$filters['angkatan'],'kelas_industri'=>$filters['kelas_industri']]);$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=attendance_input($_POST);$errors=attendance_errors($data);
    if(!$errors){try{$id=attendance_create($data,(int)$user['id']);panel_message('Kegiatan dibuat. Silakan isi absensi siswa.','panel/absensi/detail.php?id='.$id);}catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}}
}
[$years,$industries]=attendance_options();
$total=(int)panel_query('SELECT COUNT(*) FROM attendance_events e WHERE '.$where,$params)->fetchColumn();
$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT e.*,(SELECT COUNT(*) FROM attendance_records r WHERE r.event_id=e.id) AS peserta,(SELECT COUNT(*) FROM attendance_records r WHERE r.event_id=e.id AND r.status IS NOT NULL) AS tercatat FROM attendance_events e WHERE '.$where.' ORDER BY e.tanggal DESC,e.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll();
panel_start($user,'Absensi kegiatan','absensi');
?>
<div class="page-actions"><a class="button" href="<?= e(base_url('panel/ekspor.php?'.http_build_query(['jenis'=>'absensi']+$filters))) ?>">Download Excel kegiatan dan absensi</a></div>
<div class="page-actions"><h1>Absensi kegiatan</h1><a href="<?= e(base_url('panel/absensi/rekap.php?'.http_build_query($filters))) ?>">Rekap siswa</a><a href="<?= e(base_url('panel/siswa/')) ?>">Data siswa</a></div>
<?php if($errors):?><div class="alert" role="alert"><strong>Kegiatan belum dibuat.</strong><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul></div><?php endif;?>
<form class="editor-form card" method="post"><?= csrf_field() ?><h2>Buat kegiatan / pertemuan</h2>
<label for="judul">Nama kegiatan<input id="judul" name="judul" maxlength="190" value="<?= e($data['judul']) ?>" required></label>
<label for="tanggal">Tanggal kegiatan<input id="tanggal" name="tanggal" type="date" min="1900-01-01" max="2199-12-31" value="<?= e($data['tanggal']) ?>" required></label>
<label for="angkatan">Angkatan (tahun masuk)<select id="angkatan" name="angkatan" required><option value="">Pilih angkatan</option><?php foreach($years as $year):?><option value="<?= e((string)$year) ?>" <?= $data['angkatan']===(string)$year?'selected':'' ?>><?= e((string)$year) ?></option><?php endforeach;?></select></label>
<label for="kelas_industri">Kelas industri<select id="kelas_industri" name="kelas_industri" required><option value="">Pilih kelas industri</option><?php foreach($industries as $industry):?><option value="<?= e($industry) ?>" <?= $data['kelas_industri']===$industry?'selected':'' ?>><?= e($industry) ?></option><?php endforeach;?></select></label>
<label for="deskripsi">Keterangan kegiatan (opsional)<textarea id="deskripsi" name="deskripsi" rows="3" maxlength="5000"><?= e($data['deskripsi']) ?></textarea></label>
<p>Peserta diambil dari Data Siswa sesuai angkatan dan kelas industri saat kegiatan dibuat. Daftar peserta disimpan sebagai riwayat tetap. Siswa belum diberi status sampai absensi diisi.</p><button type="submit">Buat kegiatan</button></form>
<?php attendance_filter_form($filters,'panel/absensi/'); ?>
<p><?= $total ?> kegiatan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Angkatan</th><th>Kelas industri</th><th>Absensi tercatat</th><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?= e($row['judul']) ?></td><td><?= e($row['tanggal']) ?></td><td><?= e((string)$row['angkatan']) ?></td><td><?= e($row['kelas_industri']) ?></td><td><?= $row['tercatat'] ?> / <?= $row['peserta'] ?> siswa</td><td><a href="<?= e(base_url('panel/absensi/detail.php?id='.$row['id'])) ?>">Isi / edit absensi</a></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="6" class="empty-panel">Belum ada kegiatan yang sesuai.</td></tr><?php endif;?></tbody></table></div>
<nav class="pagination" aria-label="Halaman kegiatan"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/absensi/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav>
<?php panel_end($user); ?>
