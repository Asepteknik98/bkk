<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/attendance.php';
$user=require_role('admin','staf_bkk');
if(!panel_query("SHOW TABLES LIKE 'attendance_events'")->fetchColumn()){
    panel_start($user,'Absensi kegiatan','absensi');echo '<p class="alert">Jalankan pembaruan database 011_attendance.sql terlebih dahulu.</p>';panel_end($user);exit;
}
try{[$filters,$where,$params]=attendance_filters($_GET);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$groups=attendance_groups();$groupKey='';
$data=attendance_input(['tanggal'=>date('Y-m-d'),'angkatan'=>$filters['angkatan'],'kelas_industri'=>$filters['kelas_industri']]);$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=attendance_input($_POST);$groupKey=input_text($_POST,'kelompok');
    try{$data=array_replace($data,attendance_select_group($_POST,$groups));$errors=attendance_errors($data);}catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
    if(!$errors){try{$id=attendance_create($data,(int)$user['id']);panel_message('Kegiatan dibuat. Silakan isi absensi peserta.','panel/absensi/detail.php?id='.$id);}catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}}
    if($errors){http_response_code(422);}
}
if($groupKey===''){foreach($groups as $group){if((string)$group['angkatan']===$data['angkatan']&&$group['kelas_industri']===$data['kelas_industri']){$groupKey=attendance_group_key($group);break;}}}
$total=(int)panel_query('SELECT COUNT(*) FROM attendance_events e WHERE '.$where,$params)->fetchColumn();
$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT e.*,(SELECT COUNT(*) FROM attendance_records r WHERE r.event_id=e.id) AS peserta,(SELECT COUNT(*) FROM attendance_records r WHERE r.event_id=e.id AND r.status IS NOT NULL) AS tercatat FROM attendance_events e WHERE '.$where.' ORDER BY e.tanggal DESC,e.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll();
panel_start($user,'Absensi kegiatan','absensi');
?>
<div class="attendance-workspace">
<div class="page-actions"><h1><?= panel_icon('absensi') ?> Absensi kegiatan</h1><a href="<?= e(base_url('panel/absensi/rekap.php?'.http_build_query($filters))) ?>"><?= panel_icon('tracer') ?> Rekap kehadiran</a><a href="<?= e(base_url('panel/peserta/')) ?>"><?= panel_icon('peserta') ?> Data siswa</a><a class="button" href="<?= e(base_url('panel/ekspor.php?'.http_build_query(['jenis'=>'absensi']+$filters))) ?>" title="Unduh Excel kegiatan sesuai filter"><?= panel_icon('download') ?> Excel</a></div>

<ol class="attendance-steps" aria-label="Alur absensi"><li><strong>1. Siapkan siswa</strong><span>Isi angkatan dan kelas industri di Data siswa.</span></li><li><strong>2. Buat kegiatan</strong><span>Pilih kelompok siswa yang akan mengikuti kegiatan.</span></li><li><strong>3. Isi kehadiran</strong><span>Simpan Hadir, Izin, Sakit, atau Alpa, lalu lihat rekap.</span></li></ol>
<?php if($errors):?><div class="alert" role="alert"><strong>Kegiatan belum dibuat.</strong><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul></div><?php endif;?>
<details class="bmw-disclosure" id="kegiatan-form" <?= $errors||$total===0?'open':'' ?>><summary><?= panel_icon('add') ?> Buat kegiatan baru</summary>
<?php if(!$groups): ?><div class="attendance-setup"><strong><?= panel_icon('peserta') ?> Belum ada kelompok siswa yang siap diabsen</strong><p>Tambahkan siswa, lalu isi angkatan dan kelas industri pada profil siswa. Setelah itu, kembali ke halaman ini untuk membuat kegiatan.</p><a class="button" href="<?= e(base_url('panel/peserta/')) ?>"><?= panel_icon('add') ?> Lengkapi Data siswa</a></div><?php else: ?>
<form class="editor-form card attendance-create" method="post"><?= csrf_field() ?>
<label for="judul">Nama kegiatan<input id="judul" name="judul" maxlength="190" value="<?= e($data['judul']) ?>" required></label>
<label for="tanggal">Tanggal kegiatan<input id="tanggal" name="tanggal" type="date" min="1900-01-01" max="2199-12-31" value="<?= e($data['tanggal']) ?>" required></label>
<label for="kelompok" class="attendance-group-label"><span><?= panel_icon('peserta') ?> Kelompok peserta</span><select id="kelompok" name="kelompok" required aria-describedby="group-help"><option value="">Pilih angkatan dan kelas industri</option><?php foreach($groups as $group): $key=attendance_group_key($group); ?><option value="<?= e($key) ?>" <?= $groupKey===$key?'selected':'' ?>>Angkatan <?= e((string)$group['angkatan']) ?> / <?= e($group['kelas_industri']) ?> - <?= (int)$group['jumlah'] ?> siswa</option><?php endforeach; ?></select><small id="group-help">Seluruh siswa dalam kelompok ini dimasukkan otomatis. Pilihan berasal dari Data siswa.</small></label>
<label for="deskripsi">Keterangan kegiatan (opsional)<textarea id="deskripsi" name="deskripsi" rows="2" maxlength="5000"><?= e($data['deskripsi']) ?></textarea></label>
<div class="attendance-create-footer"><p>Setelah kegiatan dibuat, Anda langsung diarahkan ke daftar siswa untuk mengisi kehadiran. Kehadiran tidak terisi otomatis.</p><button type="submit"><?= panel_icon('next') ?> Buat &amp; lanjut isi kehadiran</button></div></form><?php endif; ?>
</details>
<?php attendance_filter_form($filters,'panel/absensi/'); ?>
<p><?= $total ?> kegiatan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Angkatan</th><th>Kelas industri</th><th>Progres kehadiran</th><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?= e($row['judul']) ?></td><td><?= e($row['tanggal']) ?></td><td><?= e((string)$row['angkatan']) ?></td><td><?= e($row['kelas_industri']) ?></td><td><?= $row['tercatat'] ?> / <?= $row['peserta'] ?> peserta</td><td><a href="<?= e(base_url('panel/absensi/detail.php?id='.$row['id'])) ?>"><?= panel_icon('edit') ?> Isi kehadiran</a> <a href="<?= e(base_url('panel/absensi/publikasi.php?id='.$row['id'])) ?>">Pretest &amp; poster</a></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="6" class="empty-panel"><?= $filters['q']!==''||$filters['angkatan']!==''||$filters['kelas_industri']!==''?'Tidak ada kegiatan sesuai filter. Gunakan Reset filter untuk melihat semua kegiatan.':'Belum ada kegiatan. Mulai melalui Buat kegiatan baru di atas.' ?></td></tr><?php endif;?></tbody></table></div>
<nav class="pagination" aria-label="Halaman kegiatan"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/absensi/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav>
</div>
<?php panel_end($user); ?>
