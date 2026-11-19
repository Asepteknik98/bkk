<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/speakers.php';
$user=require_role('admin','staf_bkk');
if(!panel_query("SHOW TABLES LIKE 'speakers'")->fetchColumn()){panel_start($user,'Pemateri kelas','pemateri');echo '<p class="alert">Jalankan pembaruan database 012_speakers.sql terlebih dahulu.</p>';panel_end($user);exit;}
try{[$filters,$where,$params]=speaker_filters($_GET);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$eventId=positive_id($filters['event_id']??'');
$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    try{
        if(input_text($_POST,'aksi')!=='hapus'){throw new InvalidArgumentException('Tindakan tidak valid.');}
        $deleteId=positive_id($_POST['id']??'');$version=positive_id($_POST['version']??'');
        if(!$deleteId||!$version){throw new InvalidArgumentException('Data pemateri tidak valid. Muat ulang daftar.');}
        speaker_delete($deleteId,$version);
        panel_message('Pemateri berhasil dihapus. Kegiatan dan absensi tetap tersedia.','panel/pemateri/?'.http_build_query($filters+['page'=>positive_id($_GET['page']??'1')??1]));
    }catch(InvalidArgumentException $exception){http_response_code(422);$errors[]=$exception->getMessage();}
}

$industries=panel_query("SELECT kelas_industri FROM speakers WHERE kelas_industri<>'' UNION SELECT kelas_industri FROM siswa WHERE kelas_industri<>'' UNION SELECT kelas_industri FROM attendance_events ORDER BY kelas_industri")->fetchAll(PDO::FETCH_COLUMN);
if($filters['kelas_industri']!==''&&!in_array($filters['kelas_industri'],$industries,true)){$industries[]=$filters['kelas_industri'];}
$total=(int)panel_query('SELECT COUNT(*) FROM speakers s WHERE '.$where,$params)->fetchColumn();$perPage=5;$pages=max(1,(int)ceil($total/$perPage));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT s.* FROM speakers s WHERE '.$where.' ORDER BY s.nama,s.id LIMIT :limit OFFSET :offset',$params+['limit'=>$perPage,'offset'=>($page-1)*$perPage])->fetchAll();
$speakerEvents=[];
foreach($rows as $speaker){
    $speakerEvents[(int)$speaker['id']]=panel_query('SELECT e.id,e.judul FROM attendance_events e JOIN speaker_events se ON se.event_id=e.id WHERE se.speaker_id=:id ORDER BY e.tanggal DESC,e.id DESC LIMIT 3',['id'=>(int)$speaker['id']])->fetchAll();
}
panel_start($user,'Pemateri kelas','pemateri');
?>
<div class="page-actions speaker-list-actions"><h1>Pemateri kelas</h1><a class="button" href="<?= e(base_url('panel/pemateri/form.php?'.http_build_query(array_intersect_key($filters,array_flip(['kelas_industri','event_id']))))) ?>">+ Tambah pemateri</a><a href="<?= e(base_url('panel/absensi/')) ?>">Kegiatan</a><a class="button" href="<?= e(base_url('panel/ekspor.php?'.http_build_query(['jenis'=>'pemateri']+$filters))) ?>" title="Download Excel pemateri"><?= panel_icon('download') ?> Excel</a></div>

<?php foreach($errors as $error): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<details class="bmw-filter" open><summary><?= panel_icon('filter') ?> Cari &amp; filter</summary><form class="filter-form card" method="get"><?php if($eventId):?><input type="hidden" name="event_id" value="<?= $eventId ?>"><?php endif;?><label>Cari pemateri<input name="q" maxlength="100" placeholder="Nama, instansi, atau keahlian" value="<?= e($filters['q']) ?>"></label><label>Kelas industri<select name="kelas_industri"><option value="">Semua kelas industri</option><?php foreach($industries as $industry):?><option value="<?= e($industry) ?>" <?= $filters['kelas_industri']===$industry?'selected':'' ?>><?= e($industry) ?></option><?php endforeach;?></select></label><button type="submit">Terapkan</button><a href="<?= e(base_url('panel/pemateri/')) ?>">Reset filter</a></form></details>
<?php if($eventId):?><p>Menampilkan pemateri yang terkait dengan kegiatan #<?= $eventId ?>.</p><?php endif;?>
<p>Menampilkan maksimal 5 pemateri per halaman. <?= $total ?> pemateri · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll panel-sheet speaker-compact-list" tabindex="0" role="region" aria-label="Daftar pemateri, geser ke samping jika diperlukan"><table><thead><tr><th>Pemateri</th><th>Keahlian</th><th>Kelas industri</th><th>Kegiatan</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach($rows as $row):?><tr>
<td><div class="speaker-identity"><?php if($row['foto_file']):?><img src="<?= e(base_url('panel/pemateri/berkas.php?id='.$row['id'].'&jenis=foto&v='.$row['version'])) ?>" alt="" width="32" height="32" loading="lazy"><?php else:?><span class="speaker-placeholder" aria-hidden="true"><?= panel_icon('pemateri') ?></span><?php endif;?><div><strong title="<?= e($row['nama']) ?>"><?= e($row['nama']) ?></strong><small title="<?= e($row['instansi']) ?>"><?= e($row['instansi']?:'?') ?></small></div></div></td>
<td><span class="speaker-cell-text" title="<?= e($row['keahlian']) ?>"><?= e($row['keahlian']?:'?') ?></span></td>
<td><span class="speaker-cell-text" title="<?= e($row['kelas_industri']) ?>"><?= e($row['kelas_industri']?:'?') ?></span></td><td class="speaker-event-cell"><?php $activities=$speakerEvents[(int)$row['id']]??[]; if($activities): ?><ul class="speaker-event-names"><?php foreach(array_slice($activities,0,2) as $activity): ?><li><span title="<?= e($activity['judul']) ?>"><?= e($activity['judul']) ?></span></li><?php endforeach; ?></ul><?php if(count($activities)>2): ?><a class="speaker-more-events" href="<?= e(base_url('panel/pemateri/detail.php?id='.$row['id'])) ?>">Lihat kegiatan lainnya</a><?php endif; ?><?php else: ?><span class="speaker-event-empty">Belum ada kegiatan</span><?php endif; ?></td>
<td><div class="row-actions"><a href="<?= e(base_url('panel/pemateri/detail.php?id='.$row['id'])) ?>" aria-label="<?= e('Lihat profil '.$row['nama']) ?>"><?= panel_icon('view') ?> Profil</a><a class="speaker-edit" href="<?= e(base_url('panel/pemateri/form.php?id='.$row['id'])) ?>" title="<?= e('Edit '.$row['nama']) ?>" aria-label="<?= e('Edit '.$row['nama']) ?>"><?= panel_icon('edit') ?></a><form method="post" data-confirm="<?= e('Hapus pemateri '.$row['nama'].' beserta CV dan fotonya? Kegiatan dan absensi tetap tersedia.') ?>"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="version" value="<?= (int)$row['version'] ?>"><button type="submit" class="speaker-delete danger" title="<?= e('Hapus '.$row['nama']) ?>" aria-label="<?= e('Hapus '.$row['nama']) ?>"><?= panel_icon('trash') ?></button></form></div></td>
</tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="5" class="empty-panel">Belum ada pemateri yang sesuai.</td></tr><?php endif;?></tbody></table></div>
<nav class="pagination" aria-label="Halaman pemateri"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/pemateri/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav>
<?php panel_end($user); ?>
