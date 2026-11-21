<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/participants.php';
$user=require_role('admin','staf_bkk');
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'&&input_text($_POST,'aksi')!=='hapus'){
    require __DIR__.(input_text($_POST,'aksi')==='impor'?'/impor.php':'/form.php');exit;
}
if(isset($_GET['id'])||isset($_GET['lanjut'])){
    redirect('panel/peserta/form.php?'.http_build_query(array_intersect_key($_GET,array_flip(['id','lanjut','q','angkatan','kelas_industri','urut']))));
}
try{[$filters,$where,$params,$order]=participant_filters($_GET);}
catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$participantPage='list';
$ready=(bool)panel_query("SHOW TABLES LIKE 'siswa'")->fetchColumn();
$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    try{
        $deleteId=positive_id($_POST['id']??'');
        if(!$ready||!$deleteId){throw new InvalidArgumentException('Data siswa tidak valid. Muat ulang daftar.');}
        participant_delete($deleteId);
        panel_message('Siswa berhasil dihapus.','panel/peserta/?'.http_build_query($filters+['page'=>positive_id($_GET['page']??'1')??1]));
    }catch(InvalidArgumentException $exception){http_response_code(422);$errors[]=$exception->getMessage();}
}

$angkatanOptions=$ready?panel_query('SELECT DISTINCT angkatan FROM siswa ORDER BY angkatan DESC')->fetchAll(PDO::FETCH_COLUMN):[];
$industryOptions=$ready?panel_query("SELECT DISTINCT kelas_industri FROM siswa WHERE kelas_industri<>'' ORDER BY kelas_industri")->fetchAll(PDO::FETCH_COLUMN):[];
if($filters['angkatan']!==''&&!in_array($filters['angkatan'],array_map('strval',$angkatanOptions),true)){$angkatanOptions[]=$filters['angkatan'];}
if($filters['kelas_industri']!==''&&!in_array($filters['kelas_industri'],$industryOptions,true)){$industryOptions[]=$filters['kelas_industri'];}
$total=$ready?(int)panel_query('SELECT COUNT(*) FROM siswa WHERE '.$where,$params)->fetchColumn():0;
$pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=$ready?panel_query('SELECT * FROM siswa WHERE '.$where.' ORDER BY '.$order.' LIMIT :limit OFFSET :offset',$params+['limit'=>10,'offset'=>($page-1)*10])->fetchAll():[];
$industryNames=participant_industry_names($rows);
$listColumns=array_intersect_key(participant_columns(),array_flip(['nama','kelas','angkatan','kelas_industri']));
panel_start($user,'Data siswa / peserta','peserta');
?>
<div class="pkl-page participants-workspace participants-separated participants-list-page">
<div class="page-actions participants-header"><div><h1><?= panel_icon('peserta') ?> Data siswa</h1><p>Kelola identitas siswa, kelas, angkatan, dan kelas industri.</p></div></div>
<?php require APP_ROOT.'/app/layouts/participant-navigation.php'; ?>
<?php if(!$ready): ?><p class="alert">Fitur belum aktif. Jalankan database/updates/009_participants.sql.</p><?php endif; ?>
<?php foreach($errors as $error): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<details class="bmw-filter" open><summary><?= panel_icon('filter') ?> Cari &amp; filter</summary><form class="filter-form card" method="get">
<label><span><?= panel_icon('search') ?> Cari siswa</span><input placeholder="Ketik nama siswa" name="q" maxlength="100" value="<?= e($filters['q']) ?>"></label>
<label><span><?= panel_icon('calendar') ?> Angkatan</span><select name="angkatan"><option value="">Semua angkatan</option><?php foreach($angkatanOptions as $year):?><option value="<?= e((string)$year) ?>" <?= $filters['angkatan']===(string)$year?'selected':'' ?>><?= e((string)$year) ?></option><?php endforeach;?></select></label>
<label><span><?= panel_icon('program') ?> Kelas industri</span><select name="kelas_industri"><option value="">Semua kelas industri</option><?php foreach($industryOptions as $industry):?><option value="<?= e($industry) ?>" <?= $filters['kelas_industri']===$industry?'selected':'' ?>><?= e(participant_industry_boolean($industry)?'Data lama: '.(in_array(mb_strtolower(trim($industry)),['ya','yes','true','1'],true)?'nama kelas belum diisi':'belum tercatat'):$industry) ?></option><?php endforeach;?></select></label>
<label><span><?= panel_icon('filter') ?> Urutan</span><select name="urut"><?php foreach(participant_sort_options() as $key=>$label):?><option value="<?= e($key) ?>" <?= $filters['urut']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select></label>
<button type="submit"><?= panel_icon('search') ?> Terapkan</button><a href="<?= e(base_url('panel/peserta/')) ?>"><?= panel_icon('reset') ?> Reset</a></form></details>
<div class="participants-list-heading"><h2><?= panel_icon('identity') ?> Daftar siswa</h2><a class="participants-export" href="<?= e(base_url('panel/ekspor.php?'.http_build_query(['jenis'=>'peserta']+$filters))) ?>"><?= panel_icon('download') ?> Ekspor hasil filter</a><p>Maksimal 10 siswa per halaman. <?= $total ?> siswa · Halaman <?= $page ?> dari <?= $pages ?></p></div>
<div class="table-scroll panel-sheet participant-list-sheet" data-list-size="10" tabindex="0" role="region" aria-label="Daftar siswa, gulir untuk melihat baris dan kolom lainnya"><table><thead><tr><?php foreach($listColumns as $key=>$label):?><th><?= panel_icon(['nama'=>'peserta','kelas'=>'program','angkatan'=>'calendar','kelas_industri'=>'lowongan'][$key]) ?> <?= e($key==='nama'?'Nama siswa':$label) ?></th><?php endforeach;?><th><?= panel_icon('settings') ?> Tindakan</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><?php foreach($listColumns as $key=>$label):?><td><?= e($key==='kelas_industri'?($industryNames[(int)$row['id']]??'Belum tercatat'):(string)$row[$key]) ?></td><?php endforeach;?><td><div class="row-actions participant-actions"><a href="<?= e(base_url('panel/peserta/form.php?'.http_build_query($filters+['id'=>$row['id'],'page'=>$page]))) ?>"><?= panel_icon('edit') ?> Edit</a><form method="post" data-confirm="<?= e('Hapus siswa '.$row['nama'].'? Data yang sudah memiliki riwayat tidak dapat dihapus.') ?>"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit" class="participant-delete" aria-label="<?= e('Hapus siswa '.$row['nama']) ?>"><?= panel_icon('trash') ?> Hapus</button></form></div></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="5"><?= $filters['q']!==''||$filters['angkatan']!==''||$filters['kelas_industri']!==''?'Tidak ada siswa sesuai filter. Gunakan Reset filter untuk melihat daftar lainnya.':'Belum ada siswa. Pilih menu Tambah siswa atau Impor Excel di atas.' ?></td></tr><?php endif;?></tbody></table></div>
<nav class="pagination participant-list-pagination" aria-label="Halaman peserta"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/peserta/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= $target<$page?panel_icon('back'):'' ?> <?= e($label) ?> <?= $target>$page?panel_icon('next'):'' ?></a><?php endforeach;?></nav>
</div>
<?php panel_end($user); ?>
