<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
$user=require_role('admin','staf_bkk');pkl_require_ready();
$continueTrip=input_text($_POST,'lanjut')==='perjalanan'||input_text($_GET,'lanjut')==='perjalanan';
if($continueTrip){require_permission($user,'biaya-perjalanan');}
$tripNext=$continueTrip?['lanjut'=>'perjalanan']:[];
$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=null;if($id){try{$old=pkl_find($id);}catch(InvalidArgumentException){panel_not_found();}}
$locationReady=pkl_points_ready();
$data=$old?pkl_input(array_map('strval',$old)):pkl_input(['status'=>'aktif']);$errors=[];$version=(int)($old['version']??0);
if($old&&$locationReady){$savedPoint=panel_query('SELECT latitude,longitude FROM pkl_location_points WHERE placement_id=:id',['id'=>$id])->fetch();if($savedPoint){$data=array_replace($data,$savedPoint);}}
$studentId=$old?(int)$old['siswa_id']:(positive_id($_GET['siswa_id']??'')??0);
$batchStudents=[];$batchIds=[];$isBatch=false;
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    $batchAction=input_text($_POST,'aksi');
    if(in_array($batchAction,['pilih_banyak','simpan_banyak'],true)){
        if($id){http_response_code(400);exit('Edit penempatan hanya untuk satu siswa.');}
        try{
            $batchStudents=pkl_selected_students($_POST['siswa_ids']??null);
            $batchIds=array_map(static fn($row)=>(string)$row['id'],$batchStudents);$isBatch=true;
            if($batchAction==='simpan_banyak'){
                $data=pkl_input($_POST);
                $savedIds=pkl_save_batch($batchIds,$_POST,(int)$user['id']);
                panel_message(count($savedIds).' penempatan berhasil dibuat. '.($continueTrip?'Pilih penempatan yang akan dicatat perjalanannya; biaya tidak dibuat otomatis.':''),$continueTrip?'panel/biaya-perjalanan/form.php':'panel/pkl/');
            }
        }catch(Throwable $exception){$errors[]=pkl_error($exception);}
    }else{
        $data=pkl_input($_POST);$studentId=positive_id($data['siswa_id'])??0;
        $version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
        try{$saved=pkl_save($data,$id,$version,(int)$user['id']);panel_message('Penempatan PKL berhasil disimpan.',$continueTrip?'panel/biaya-perjalanan/form.php?placement_id='.$saved:'panel/pkl/detail.php?id='.$saved);}
        catch(Throwable $exception){$errors[]=pkl_error($exception);}
    }
}
$student=$studentId?panel_query('SELECT * FROM siswa WHERE id=:id',['id'=>$studentId])->fetch():false;
if($isBatch){$student=['nama'=>count($batchStudents).' siswa dipilih','tempat_pkl'=>'Mengikuti penempatan baru','tahun_pkl'=>''];}
if(!$old&&$student&&($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){$data['siswa_id']=(string)$student['id'];$data['kelas']=$student['kelas'];$data['jurusan']=$student['jurusan'];$data['tempat_pkl']=$student['tempat_pkl'];}
$search=input_text($_GET,'q_siswa');if(!valid_text($search,100,false)){http_response_code(400);exit('Pencarian siswa terlalu panjang.');}
$students=[];
if(!$student){$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$search).'%';$students=panel_query("SELECT id,nis,nama,kelas,jurusan FROM siswa WHERE nama LIKE :nama ESCAPE '!' OR nis LIKE :nis ESCAPE '!' ORDER BY nama,id LIMIT 50",['nama'=>$pattern,'nis'=>$pattern])->fetchAll();}
panel_start($user,$id?'Edit penempatan PKL':'Tambah penempatan PKL','pkl');
?>
<div class="pkl-placement-workspace"><div class="page-actions"><h1><?= panel_icon('program') ?> <?= $id?'Edit':'Tambah' ?> penempatan PKL</h1><div class="pkl-student-actions"><?php if(!$student): ?><a class="button" href="<?= e(base_url('panel/peserta/form.php?'.http_build_query(['lanjut'=>'pkl']+($continueTrip?['kembali'=>'perjalanan']:[])).'#peserta-form')) ?>"><?= panel_icon('add') ?> Tambah siswa baru</a><?php endif; ?><a href="<?= e(base_url('panel/pkl/')) ?>"><?= panel_icon('back') ?> Daftar PKL</a></div></div>
<?php if($continueTrip): ?><p class="notice">Alur perjalanan: pilih siswa, simpan penempatan, lalu isi biaya perjalanan.</p><?php endif; ?>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if(!$student): ?>
<p class="pkl-picker-help">Pilih siswa untuk mengisi penempatan PKL. Belum terdaftar? Gunakan <strong>Tambah siswa baru</strong>.</p>
<form class="filter-form card" method="get"><?php if($continueTrip): ?><input type="hidden" name="lanjut" value="perjalanan"><?php endif; ?><label for="pkl-student-search">Cari nama siswa<input id="pkl-student-search" name="q_siswa" maxlength="100" value="<?= e($search) ?>"></label><button type="submit">Cari siswa</button><?php if($search!==''): ?><a href="<?= e(base_url('panel/pkl/form.php'.($continueTrip?'?lanjut=perjalanan':''))) ?>">Tampilkan semua siswa</a><?php endif; ?></form>
<div class="pkl-picker-heading"><h2><?= panel_icon('peserta') ?> Daftar siswa</h2><span><?= count($students) ?> hasil &middot; Maksimal 50; gunakan pencarian untuk siswa lain.</span></div>
<form method="post" class="pkl-multi-picker"><?= csrf_field() ?><input type="hidden" name="aksi" value="pilih_banyak"><?php if($continueTrip): ?><input type="hidden" name="lanjut" value="perjalanan"><?php endif; ?>
<div class="pkl-multi-toolbar"><span><strong id="pkl-selected-count" role="status">0 siswa dipilih</strong> &middot; Pilihan tetap saat berganti slide pada hasil ini.</span><button type="button" data-pkl-check="all">Centang semua hasil</button><button type="button" data-pkl-check="none">Kosongkan</button><button type="submit"><?= panel_icon('next') ?> Lanjutkan pilihan</button></div>
<div class="table-scroll pkl-student-sheet" data-list-size="10" tabindex="0" role="region" aria-label="Pilih siswa, gulir untuk melihat data"><table><thead><tr><th>Siswa</th><th>Kelas / jurusan</th><th>Tindakan</th></tr></thead><tbody><?php foreach($students as $item): ?><tr><td><label class="pkl-multi-choice"><input type="checkbox" name="siswa_ids[]" value="<?= (int)$item['id'] ?>"><span><?= e($item['nama']) ?></span></label></td><td><?= e($item['kelas'].' / '.$item['jurusan']) ?></td><td><a href="<?= e(base_url('panel/pkl/form.php?'.http_build_query(['siswa_id'=>$item['id']]+$tripNext))) ?>" class="pkl-pick-student"><?= panel_icon('next') ?> Pilih siswa</a></td></tr><?php endforeach; ?><?php if(!$students): ?><tr><td colspan="3" class="pkl-student-empty"><strong><?= $search!==''?'Tidak ada siswa yang sesuai pencarian.':'Belum ada data siswa.' ?></strong><p><?= $search!==''?'Ubah kata kunci atau tambahkan siswa yang belum terdaftar.':'Tambahkan siswa terlebih dahulu, lalu lengkapi tempat, periode, dan pembimbing PKL.' ?></p><a class="button" href="<?= e(base_url('panel/peserta/form.php?'.http_build_query(['lanjut'=>'pkl']+($continueTrip?['kembali'=>'perjalanan']:[])).'#peserta-form')) ?>"><?= panel_icon('add') ?> Tambah siswa baru</a></td></tr><?php endif; ?></tbody></table></div></form>
<?php else: ?>
<div class="pkl-selected-student"><div><strong><?= panel_icon('peserta') ?> <?= e($student['nama']) ?></strong><?php if(!$old&&!$isBatch): ?><small>Riwayat siswa: <?= e($student['tempat_pkl']?:'Tempat belum diisi') ?> &middot; <?= e($student['tahun_pkl']?:'Tahun belum diisi') ?></small><?php endif; ?></div><?php if(!$old): ?><a href="<?= e(base_url('panel/pkl/form.php'.($continueTrip?'?lanjut=perjalanan':''))) ?>"><?= panel_icon('reset') ?> Ganti siswa</a><?php endif; ?></div>
<?php if($isBatch): ?><details class="pkl-batch-preview"><summary><?= panel_icon('peserta') ?> Rincian <?= count($batchStudents) ?> siswa terpilih</summary><div class="table-scroll" data-vertical-list><table><thead><tr><th>Siswa</th><th>Kelas / jurusan</th></tr></thead><tbody><?php foreach($batchStudents as $member): ?><tr><td><?= e($member['nama']) ?></td><td><?= e($member['kelas'].' / '.$member['jurusan']) ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endif; ?>
<form class="editor-form card pkl-placement-editor" method="post"><?= csrf_field() ?><?php if($isBatch): ?><input type="hidden" name="aksi" value="simpan_banyak"><?php foreach($batchIds as $batchId): ?><input type="hidden" name="siswa_ids[]" value="<?= e($batchId) ?>"><?php endforeach; ?><?php endif; ?><?php if($continueTrip): ?><input type="hidden" name="lanjut" value="perjalanan"><?php endif; ?><input type="hidden" name="siswa_id" value="<?= e($data['siswa_id']) ?>"><input type="hidden" name="version" value="<?= $version ?>">
<?php $pklFieldIcons=['kelas'=>'peserta','jurusan'=>'program','tanggal_mulai'=>'calendar','tanggal_selesai'=>'calendar','tempat_pkl'=>'lowongan','alamat_lengkap'=>'location','guru_pembimbing'=>'akun','pembimbing_industri'=>'pengguna','status'=>'check-circle']; ?>
<?php foreach(pkl_fields() as $key=>[$label,$max]): if($isBatch&&in_array($key,['kelas','jurusan'],true)){continue;} ?>
<?php if($key==='kelas'||($isBatch&&$key==='tanggal_mulai')): ?><h2><?= panel_icon('calendar') ?> 1. <?= $isBatch?'Periode PKL':'Kelas &amp; periode PKL' ?></h2><?php elseif($key==='tempat_pkl'): ?><h2><?= panel_icon('location') ?> 2. Tempat pelaksanaan</h2><?php elseif($key==='guru_pembimbing'): ?><h2><?= panel_icon('pengguna') ?> 3. Pembimbing &amp; status</h2><?php endif; ?>
<?php if($key==='tempat_pkl'){require APP_ROOT.'/app/layouts/pkl-location-picker.php';} ?>
<div class="pkl-placement-cell <?= in_array($key,['tempat_pkl','alamat_lengkap','status'],true)?'pkl-placement-wide':'' ?>"><label for="<?= $key ?>"><?= panel_icon($pklFieldIcons[$key]) ?> <?= e($label) ?><?= !in_array($key,['kelas','jurusan','status'],true)?' *':'' ?></label>
<?php if($key==='alamat_lengkap'): ?><textarea id="<?= $key ?>" name="<?= $key ?>" maxlength="<?= $max ?>" rows="2" required placeholder="Jalan, desa/kelurahan, kecamatan, kabupaten/kota, provinsi, kode pos"><?= e($data[$key]) ?></textarea>
<?php elseif($key==='status'): ?><select id="status" name="status"><?php foreach(['aktif'=>'Aktif','selesai'=>'Selesai','arsip'=>'Arsip'] as $keyStatus=>$labelStatus): ?><option value="<?= $keyStatus ?>" <?= $data['status']===$keyStatus?'selected':'' ?>><?= $labelStatus ?></option><?php endforeach; ?></select>
<?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= str_starts_with($key,'tanggal_')?'date':'text' ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= in_array($key,['kelas','jurusan'],true)?'':'required' ?>><?php endif; ?></div><?php endforeach; ?>
<div class="pkl-placement-footer"><small>* Wajib diisi. <?= $isBatch?'Kelas/jurusan mengikuti data masing-masing siswa.':'Isi minimal kelas atau jurusan.' ?> Nama pembimbing tidak membuat akun.</small><button type="submit"><?= panel_icon('save') ?> <?= $isBatch?'Simpan '.count($batchStudents).' penempatan':($continueTrip?'Simpan & lanjut ke perjalanan':'Simpan penempatan') ?></button></div></form><?php endif; ?>
</div>
<?php if($student): ?><link rel="stylesheet" href="<?= e(asset_url('vendor/leaflet/leaflet.css')) ?>"><script src="<?= e(asset_url('vendor/leaflet/leaflet.js')) ?>" defer></script><script src="<?= e(asset_url('js/pkl-location.js')) ?>?v=<?= (int)filemtime(APP_ROOT.'/assets/js/pkl-location.js') ?>" defer></script><?php endif; ?>
<?php if(!$student): ?><script src="<?= e(asset_url('js/pkl-selection.js')) ?>?v=<?= (int)filemtime(APP_ROOT.'/assets/js/pkl-selection.js') ?>" defer></script><?php endif; ?>
<?php panel_end($user); ?>
