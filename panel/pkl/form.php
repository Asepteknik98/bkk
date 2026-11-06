<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
$user=require_role('admin','staf_bkk');pkl_require_ready();
$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=null;if($id){try{$old=pkl_find($id);}catch(InvalidArgumentException){panel_not_found();}}
$data=$old?pkl_input(array_map('strval',$old)):pkl_input(['status'=>'aktif']);$errors=[];$version=(int)($old['version']??0);
$studentId=$old?(int)$old['siswa_id']:(positive_id($_GET['siswa_id']??'')??0);
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=pkl_input($_POST);$studentId=positive_id($data['siswa_id'])??0;
    $version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{$saved=pkl_save($data,$id,$version,(int)$user['id']);panel_message('Penempatan PKL berhasil disimpan.','panel/pkl/detail.php?id='.$saved);}
    catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
$student=$studentId?panel_query('SELECT * FROM siswa WHERE id=:id',['id'=>$studentId])->fetch():false;
if(!$old&&$student&&($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){$data['siswa_id']=(string)$student['id'];$data['kelas']=$student['kelas'];$data['jurusan']=$student['jurusan'];$data['tempat_pkl']=$student['tempat_pkl'];}
$search=input_text($_GET,'q_siswa');if(!valid_text($search,100,false)){http_response_code(400);exit('Pencarian siswa terlalu panjang.');}
$students=[];
if(!$student){$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$search).'%';$students=panel_query("SELECT id,nis,nama,kelas,jurusan FROM siswa WHERE nama LIKE :nama ESCAPE '!' OR nis LIKE :nis ESCAPE '!' ORDER BY nama,id LIMIT 50",['nama'=>$pattern,'nis'=>$pattern])->fetchAll();}
panel_start($user,$id?'Edit penempatan PKL':'Tambah penempatan PKL','pkl');
?>
<div class="page-actions"><h1><?= $id?'Edit':'Tambah' ?> penempatan PKL</h1><a href="<?= e(base_url('panel/pkl/')) ?>">Daftar PKL</a></div>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if(!$student): ?>
<p>Pilih siswa dari data peserta yang sudah ada. Jika belum terdaftar, tambahkan melalui <a href="<?= e(base_url('panel/peserta/')) ?>">Data siswa / peserta</a>.</p>
<form class="filter-form card" method="get"><label>Cari nama atau NIS<input name="q_siswa" maxlength="100" value="<?= e($search) ?>"></label><button>Cari siswa</button></form>
<p>Menampilkan maksimal 50 siswa. Persempit pencarian untuk menemukan siswa lain.</p>
<div class="table-scroll"><table><thead><tr><th>Siswa</th><th>NIS</th><th>Kelas / jurusan</th><th>Pilih</th></tr></thead><tbody><?php foreach($students as $item): ?><tr><td><?= e($item['nama']) ?></td><td><?= e($item['nis']) ?></td><td><?= e($item['kelas'].' / '.$item['jurusan']) ?></td><td><a href="<?= e(base_url('panel/pkl/form.php?siswa_id='.$item['id'])) ?>">Pilih siswa</a></td></tr><?php endforeach; ?><?php if(!$students): ?><tr><td colspan="4">Siswa tidak ditemukan.</td></tr><?php endif; ?></tbody></table></div>
<?php else: ?>
<p><strong><?= e($student['nama']) ?></strong> · NIS <?= e($student['nis']) ?></p>
<?php if(!$old): ?><p>Riwayat lama: <?= e($student['tempat_pkl']?:'Tempat belum diisi') ?> · <?= e($student['tahun_pkl']?:'Tahun belum diisi') ?>. Lengkapi periode dan alamat untuk penempatan ini.</p><a href="<?= e(base_url('panel/pkl/form.php')) ?>">Pilih siswa lain</a><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="siswa_id" value="<?= e($data['siswa_id']) ?>"><input type="hidden" name="version" value="<?= $version ?>">
<p>Kelas atau jurusan wajib diisi. Guru pembimbing dicatat sebagai penugasan; tidak dibuatkan akun.</p>
<?php foreach(pkl_fields() as $key=>[$label,$max]): ?><label for="<?= $key ?>"><?= e($label) ?>
<?php if($key==='alamat_lengkap'): ?><textarea id="<?= $key ?>" name="<?= $key ?>" maxlength="<?= $max ?>" rows="3" required placeholder="Jalan, nomor, desa/kelurahan, kecamatan, kabupaten/kota, provinsi, kode pos"><?= e($data[$key]) ?></textarea>
<?php elseif($key==='status'): ?><select id="status" name="status"><?php foreach(['aktif'=>'Aktif','selesai'=>'Selesai','arsip'=>'Arsip'] as $keyStatus=>$labelStatus): ?><option value="<?= $keyStatus ?>" <?= $data['status']===$keyStatus?'selected':'' ?>><?= $labelStatus ?></option><?php endforeach; ?></select>
<?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= str_starts_with($key,'tanggal_')?'date':'text' ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= in_array($key,['kelas','jurusan'],true)?'':'required' ?>><?php endif; ?></label><?php endforeach; ?>
<button>Simpan penempatan</button></form><?php endif; ?>
<?php panel_end($user); ?>
